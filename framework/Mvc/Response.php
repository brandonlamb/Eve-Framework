<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @package Eve\Request
 */
namespace Eve\Mvc;

class Response extends Component
{
	/**
	 * The content body of the response.
	 *
	 * @var mixed
	 */
	protected $_body;

	/**
	 * The HTTP status code of the response.
	 *
	 * @var int
	 */
	protected $_status = 200;

	/**
	 * The response headers.
	 *
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * HTTP status codes.
	 *
	 * @var array
	 */
	protected static $_statuses = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded'
	);

	/**
	 * Setter for response content body
	 *
	 * @param mixed $value
	 * @return Response
	 */
	public function setBody($value)
	{
		$this->_body = $value;
		return $this;
	}

	/**
	 * Get response content body
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * Clear response body
	 *
	 * @return Response
	 */
	public function clear()
	{
		$this->_body = null;
		return $this;
	}

	/**
	 * Setter for status code
	 *
	 * @param int $value
	 * @return Response
	 */
	public function setStatus($value)
	{
		$value = (int) $value;
		if (isset(static::$_statuses[$value])) {
			$this->_status = $value;
		}
		return $this;
	}

	/**
	 * Getter for status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return (int) $this->_status;
	}

	/**
	 * Send the response to the browser.
	 *
	 * @return void
	 */
	public function send()
	{
		if (!isset($this->_headers['Content-Type'])) {
			$this->setHeader('Content-Type', 'text/html; charset=utf-8');
		}

		if (!headers_sent()) { $this->sendHeaders(); }

		echo $this->_body;
	}

	/**
	 * Setter for adding/removing a header to/from the response.
	 *
	 * @param string $key
	 * @param string $value
	 * @return Response
	 */
	public function setHeader($key, $value, $replace = true)
	{
		if ($replace === true) {
			$this->_headers[$key] = $value;
		} else {
			$this->_headers[] = array($key, $value);
		}
		return $this;
	}

	/**
	 * Getter for header
	 *
	 * @param string $key
	 * @return string
	 */
	public function getHeader($key)
	{
		return (isset($this->_headers[$key])) ? $this->_headers[$key] : null;
	}

	/**
	 * Fetch response headers. If optional bool parameter is passed as true, compile into a string
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return (array) $this->_headers;
	}

	/**
	 * Sends the headers if they haven't already been sent. Returns whether they were sent or not.
	 *
	 * @return bool
	 */
	public function sendHeaders()
	{
		if (!headers_sent()) {
			// Send the protocol/status line first, FCGI servers need different status header
			if (!empty($_SERVER['FCGI_SERVER_VERSION']) || !empty($_SERVER['FCGI_ROLE'])) {
				header('Status: ' . $this->_status . ' ' . static::$_statuses[$this->_status]);
			} else {
				$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
				header($protocol . ' ' . $this->_status . ' ' . static::$_statuses[$this->_status]);
			}

			foreach ($this->_headers as $name => $value) {
				// Parse non-replace headers
				is_int($name) && is_array($value) && list($name, $value) = $value;

				// Create the header
				is_string($name) && $value = $name . ': ' . $value;

				// Send it
				header($value, true);
			}
			return true;
		}
		return false;
	}

	/**
	 * Determine if the response is a redirect.
	 *
	 * @return bool
	 */
	public function isRedirect()
	{
		return $this->_status === 301 || $this->_status === 302;
	}

	/**
	 * Redirects to another uri/url.  Sets the redirect header,
	 * sends the headers and exits.  Can redirect via a Location header
	 * or using a refresh header.
	 *
	 * The refresh header works better on certain servers like IIS.
	 *
	 * @param string $url The url
	 * @param string $method The redirect method
	 * @param int $code The redirect status code
	 * @return void
	 */
	public static function redirect($url = '', $method = 'location', $code = 302)
	{
		$response = new static();
		$response->status($code);

		if ($method == 'location')
		{
			$response->header('Location', $url);
		} else if ($method == 'refresh') {
			$response->header('Refresh', '0;url=' . $url);
		} else {
			return;
		}

#		\Event::shutdown();

		$response->send();
		exit;
	}

	/**
	 * Returns the body as a string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->getBody();
	}
}
