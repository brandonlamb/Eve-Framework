<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @package Eve\Request
 */
namespace Eve\Mvc;

class Request extends Component
{
#	const SCHEME_HTTP		= 'http';
#	const SCHEME_HTTPS		= 'https';
	const URI_REGEX			= '/[^a-zA-Z0-9_\-\/\s]/';

	/**
	 * @var Request\Method
	 */
	protected $_method;

	protected $_body;
	protected $_query;
	protected $_fragment;
	protected $_module;
	protected $_controller;
	protected $_action;
	protected $_baseUri;
	protected $_uri;
	protected $_params;
	protected $_language;
	protected $_exception;
	protected $_dispatched;
	protected $_routed;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config)
	{
		parent::__construct($config);

		$this->_body = @file_get_contents('php://input');
		$uri = null;

		do {
			// Use HTTP_X_REWRITE_URL
			if (isset($_SERVER['HTTP_X_REWRITE_URL']) && null !== $_SERVER['HTTP_X_REWRITE_URL'])
			{
				$uri = $_SERVER['HTTP_X_REWRITE_URL'];
				break;
			}

			// Use IIS_WasUrlRewritten
			if (isset($_SERVER['IIS_WasUrlRewritten']) && null !== $_SERVER['IIS_WasUrlRewritten'])
			{
				$uri = $_SERVER['IIS_WasUrlRewritten'];
				break;
			}

			// Use REQUEST_URI
			if (isset($_SERVER['REQUEST_URI']) && null !== $_SERVER['REQUEST_URI'])
			{
				$uri = $_SERVER['REQUEST_URI'];
				break;
			}

			// Use PATH_INFO
			if (isset($_SERVER['PATH_INFO']) && null !== $_SERVER['PATH_INFO'])
			{
				$uri = $_SERVER['PATH_INFO'];
				break;
			}

			// Use PATH_INFO
			if (isset($_SERVER['ORIG_PATH_INFO']) && null !== $_SERVER['ORIG_PATH_INFO'])
			{
				$uri = $_SERVER['ORIG_PATH_INFO'];
				break;
			}
		} while (0);

		// Set the uri and base uri
		if (null !== $uri) {
			$this->_uri = preg_replace(self::URI_REGEX, '', parse_url($uri, PHP_URL_PATH));
			$parsedUri = parse_url($_SERVER['REQUEST_URI']);
			$this->_query = (isset($parsedUri['query'])) ? $parsedUri['query'] : null;
			$this->_fragment = (isset($parsedUri['fragment'])) ? $parsedUri['fragment'] : null;
			$this->_setBaseUri($this->_uri);
		}
	}

	/**
	 * Get HTTP request body
	 *
	 * @return string|false
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * Set the query string
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setQuery($value = null)
	{
		if (null !== $value && is_string($value)) { $this->_query = $value; }
		return $this;
	}

	/**
	 * Get the query string
	 *
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->_query;
	}

	/**
	 * Set the fragment string
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setFragment($value = null)
	{
		if (null !== $value && is_string($value)) {	$this->_fragment = $value; }
		return $this;
	}

	/**
	 * Get the fragment string
	 *
	 * @return string
	 */
	public function getFragment()
	{
		return $this->_fragment;
	}

	/**
	 * Return a cookie key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getCookie($key, $default = null)
	{
		return (is_array($_COOKIES) && isset($_COOKIES[$key])) ? \Eve\Filter::xss($_COOKIES[$key]) : $default;
	}

	/**
	 * Return a file key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return array
	 */
	public function getFile($key, $default = null)
	{
		return (is_array($_FILES) && isset($_FILES[$key])) ? \Eve\Filter::xss($_FILES[$key]) : $default;
	}

	/**
	 * Return a post key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($key, $default = null)
	{
		return (is_array($_POST) && isset($_POST[$key])) ? \Eve\Filter::xss($_POST[$key]) : $default;
	}

	/**
	 * Return a get query key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getQuery($key, $default = null)
	{
		return (is_array($_GET) && isset($_GET[$key])) ? \Eve\Filter::xss($_GET[$key]) : $default;
	}

	/**
	 * Return a request key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getRequest($key, $default = null)
	{
		return (is_array($_REQUEST) && isset($_REQUEST[$key])) ? \Eve\Filter::xss($_REQUEST[$key]) : $default;
	}

	/**
	 * Set base uri from uri
	 *
	 * @todo Is this even used? probably just remove this method
	 * @param string $value
	 * @return void
	 */
	protected function _setBaseUri($value)
	{
		$scriptFilename = (isset($_SERVER['SCRIPT_FILENAME'])) ? $_SERVER['SCRIPT_FILENAME'] : null;
		$ext = 'php';

		do {
			if (!is_string($scriptFilename)) { break; }

			$scriptFilename = basename($scriptFilename, $ext);
			$scriptName = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : null;

			if (is_string($scriptName)) { $scriptFilename = basename($scriptName); }
		} while (0);
	}

	/**
	 * Set the module name
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setModule($value = null)
	{
		if (null !== $value) { $this->_module = (string) $value; }
		return $this;
	}

	/**
	 * Get the module name
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->_module;
	}

	/**
	 * Set the controller name
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setController($value = null)
	{
		if (null !== $value) { $this->_controller = (string) $value; }
		return $this;
	}

	/**
	 * Get the controller name
	 *
	 * @return string
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * Set the action name
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setAction($value = null)
	{
		if (null !== $value) { $this->_action = (string) $value; }
		return $this;
	}

	/**
	 * Get the action name
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * Set the base uri
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setBaseUri($value = null)
	{
		if (null !== $value && strlen($value)) { $this->_baseUri = (string) $value; }
		return $this;
	}

	/**
	 * Get the base uri
	 *
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->_baseUri;
	}

	/**
	 * Set the request uri
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setUri($value = null)
	{
		if (null !== $value) { $this->_uri = (string) $value; }
		return $this;
	}

	/**
	 * Get or get the request uri
	 *
	 * @return string
	 */
	public function getUri()
	{
		return $this->_uri;
	}

	/**
	 * Set a parameter
	 *
	 * @param string $key
	 * @param string $value
	 * @return Request
	 */
	public function setParam($key, $value = null)
	{
		if (null !== $value) { $this->_params[$key] = (string) $value; }
		return $this;
	}

	/**
	 * Get a parameter
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($key, $default = null)
	{
		return (isset($this->_params[$key])) ? $this->_params[$key] : $default;
	}

	/**
	 * Set dispatched flag
	 *
	 * @param bool $value
	 * @return Request
	 */
	public function setDispatched($value)
	{
		$this->_dispatched = (bool) $value;
		return $this;
	}

	/**
	 * Get dispatched flag
	 *
	 * @return bool
	 */
	public function isDispatched()
	{
		return (bool) $this->_dispatched;
	}

	/**
	 * Set routed flag
	 *
	 * @param bool $value
	 * @return Request
	 */
	public function setRouted($value)
	{
		$this->_routed = (bool) $value;
		return $this;
	}

	/**
	 * Get routed flag
	 *
	 * @return bool
	 */
	public function isRouted()
	{
		return (bool) $this->_routed;
	}

	/**
	 * Set the exception object
	 *
	 * @param \Exception $exception
	 * @return Request
	 */
	public function setException(\Exception $exception = null)
	{
		if (null !== $exception) { $this->_exception = $exception; }
		return $this;
	}

	/**
	 * Get the exception object
	 *
	 * @return \Exception
	 */
	public function getException()
	{
		return (is_object($this->_exception)) ? $this->_exception : null;
	}

	/**
	 * Set parameters array
	 *
	 * @param array $value
	 * @return array
	 */
	public function setParams($value = null)
	{
		if (null !== $value && is_array($value)) { $this->_params = $value; }
		return $this;
	}

	/**
	 * Get parameters array
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Set language
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setLanguage($value = null)
	{
		if (null !== $value) { $this->_language = (string) $value; }
		return $this;
	}

	/**
	 * Get language
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return (string) $this->_language;
	}

	/**
	 * Return a server key or default
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getServer($key, $default = null)
	{
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}

	/**
	 * Creates a Request\Method object and returns it for chaining isGet() type methods
	 *
	 * @return Request\Method
	 */
	public function getMethod()
	{
		if (null === $this->_method) {
			$this->_method = new Request\Method();
		}
		return $this->_method;
	}
}
