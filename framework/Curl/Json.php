<?php
namespace Eve\Curl;

class Json
{
	protected $_headers	= array();
	protected $_options = array();
	protected $_curl	= null;
	protected $_retry	= 3;
	protected $_response = null;

	/**
	 * Preset array of possible API endpoints
	 */
	protected $_endpoints = array();

	/**
	 * Initialize curl resource
	 * @param string $url, url to post to
	 * @param string $data, post data
	 */
	public function __construct($url, $data = array())
	{
		$this->_headers = array(
			'Accept: text/html,text/xml,application/xhtml+xml,application/xml,application/json;q=0.9,*/*;q=0.8',
			'Accept-Language: en-gb,en;q=0.5',
			'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
			'Keep-Alive: 300',
			'Cache-Control: max-age=0',
			'Connection: keep-alive',
		);

		$this->_options = array(
			CURLOPT_HTTPHEADER		=> $this->_headers,
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
			CURLOPT_POST			=> true,
			CURLOPT_POSTFIELDS		=> http_build_query($data),
		);

		$this->_curl = curl_init($url);
		curl_setopt_array($this->_curl, $this->_options);
	}

	/**
	 * Executes a single cURL session
	 * @param $key int, id of session to execute
	 * @return array of content if CURLOPT_RETURNTRANSFER is set
	 */
	public function exec()
	{
		if ($this->_retry > 0) {
			$retry = $this->_retry;
			$code = 0;

			while ($retry >= 0 && ($code[0] == 0 || $code[0] >= 400)) {
				$this->_response = curl_exec($this->_curl);
				$code = $this->info(CURLINFO_HTTP_CODE);

				$retry--;
			}
		} else {
			$this->_response = curl_exec($this->_curl);
		}

		return json_decode(trim($this->_response));
	}

	/**
	 * Returns an array of session information
	 *
	 * @param bool $opt, optional option to return
	 * @return string
	 */
	public function info($opt = false)
	{
		$info[] = ($opt === true) ? curl_getinfo($this->_curl, $opt) : curl_getinfo($this->_curl);

		return $info;
	}

	/**
	 * Return raw results
	 *
	 * @return string
	 */
	public function response()
	{
		return $this->_response;
	}
}
