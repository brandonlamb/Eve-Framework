<?php
/**
 * Eve Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package Eve
 * @version 0.2.0
 */
namespace Eve;

#class Rest extends Resource
class Rest
{
    /**
     * Base URL of the REST server
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Parser to handle responses
     *
     * @var REST\Parser
     */
    protected $parser;

    /**
     * Response of last call
     *
     * @var string
     */
    protected $response;

    /**
     * Connection retry count
     *
     * @var int
     */
    protected $retry = 0;

    /**
     * Default cURL options
     *
     * @var array
     */
    protected $options = array(
        CURLOPT_HTTPHEADER		=> array(),
        CURLOPT_USERAGENT		=> 'Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.8.1.14) Gecko/20080418 Ubuntu/7.10 (gutsy) Firefox/2.0.0.14',
        CURLOPT_SSL_VERIFYHOST	=> 2,
        CURLOPT_SSL_VERIFYPEER	=> 0,
        CURLOPT_FOLLOWLOCATION	=> true,
        CURLOPT_RETURNTRANSFER	=> true,
        CURLOPT_TIMEOUT			=> 15,
        CURLOPT_HEADER			=> false,
        CURLOPT_AUTOREFERER		=> true,
        CURLOPT_FOLLOWLOCATION	=> true,
        CURLOPT_MAXREDIRS		=> 1,
    );

    /**
     * Defaults cURL headers
     *
     * @var array
     */
    protected $headers = array(
        'Accept'			=> '*/*',
        'Accept-Language'	=> 'en-gb,en;q=0.5',
        'Accept-Charset'	=> 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
        'Keep-Alive'		=> '300',
        'Cache-Control'		=> 'max-age=0',
        'Connection'		=> 'keep-alive',
    );

    /**
     * HTTP errors that are likely to be returned by a REST service
     *
     * @var array
     */
    protected $errors = array(
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        503 => 'Service Unavailable'
    );

    /**
     * Set REST server URL and response parser
     *
     * @param string $url
     */
    public function __construct($baseUrl = null, Rest\ParserInterface $parser = null)
    {
        $this->baseUrl = $baseUrl;
        $this->parser = $parser;
    }

    /**
     * Set options for basic auth
     *
     * @param string $username
     * @param string $password
     */
    public function setBasicAuth($username, $password)
    {
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $this->options[CURLOPT_USERPWD] = $username . ':' . $password;
    }

    /**
     * Perform a GET request
     *
     * @param  string $path
     * @return mixed
     */
    public function get($path)
    {
        return $this->request($this->baseUrl . $path);
    }

    /**
     * Perform a POST request
     *
     * @param  string         $path
     * @param  string | array $data
     * @return mixed
     */
    public function post($path, $data = null)
    {
        $options = array(CURLOPT_POST => true);
        if (null !== $data) {
#			$options[CURLOPT_POSTFIELDS] = http_build_query($data);
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        return $this->request($this->baseUrl . $path, $options);
    }

    /**
     * Perform a PUT request
     *
     * @param  string         $path
     * @param  string | array $data
     * @return mixed
     */
    public function put($path, $data = null)
    {
        $options = array(CURLOPT_PUT => true);
        if (null !== $data) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        return $this->request($this->baseUrl . $path, $options);
    }

    /**
     * Perform a DELETE request
     *
     * @param  string $path
     * @return mixed
     */
    public function delete($path)
    {
        return $this->request($this->baseUrl . $path, array(CURLOPT_CUSTOMREQUEST => 'DELETE'));
    }

    /**
     * Get the response of the most recent request
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get a parsed version of the response of the most recent request
     *
     * @return mixed
     */
    public function getParsedResponse()
    {
        if ($this->parser) {
            return $this->parser->parse($this->response);
        }

        return $this->response;
    }

    /**
     * Send request
     *
     * @param string $url
     * @param array  $options
     */
    protected function request($url, $options = array())
    {
        // Merge options
        $options += $this->options;
        $options[CURLOPT_HTTPHEADER] = $this->getHeaders();

        // Send cURL request
        $curl = curl_init($url);
        curl_setopt_array($curl, $options);

        if ($this->retry > 0) {
            $retry = $this->retry;
            $code = 0;

            while ($retry >= 0 && ($code[0] === 0 || $code[0] >= 400)) {
                $this->response = curl_exec($curl);
                $meta = curl_getinfo($curl);
                $code = $meta[CURLINFO_HTTP_CODE];

                $retry--;
            }
        } else {
            $this->response = curl_exec($curl);
            $meta = curl_getinfo($curl);
        }

        curl_close($curl);

        // Check for errors
        $this->checkResult($meta);

        // Return parsed response if a parser was specified, otherwise send the raw response
        if ($this->parser instanceof Rest\ParserInterface) {
            return $this->parser->parse($this->response);
        } else {
            return $this->response;
        }
    }

    /**
     * Set a header value
     *
     * @param  string $key
     * @param  string $value
     * @return Rest
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = (string) $value;

        return $this;
    }

    /**
     * Return a header value
     *
     * @param  string $key
     * @return string
     */
    public function getHeader($key)
    {
        return (isset($this->headers[$key])) ? $this->headers[$key] : null;
    }

    /**
     * Return numerically indexed array of headers in key: value format
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        return $headers;
    }

    /**
     * Set number of retries
     *
     * @param int
     * @return Rest
     */
    public function setRetry($value)
    {
        $this->retry = (int) $value;

        return $this;
    }

    /**
     * Get retry value
     *
     * @return int
     */
    public function getRetry()
    {
        return (int) $this->retry;
    }

    /**
     * Set base URL
     *
     * @param string
     * @return Rest
     */
    public function setBaseUrl($value)
    {
        $this->baseUrl = (string) $value;

        return $this;
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return (string) $this->baseUrl;
    }

    /**
     * Check result for errors
     *
     * @param  array     $meta
     * @throws Exception
     */
    protected function checkResult($meta)
    {
        if (array_key_exists($meta['http_code'], $this->errors)) {
            throw new REST\Exception($this->errors[$meta['http_code']], $meta['http_code']);
        }
    }
}
