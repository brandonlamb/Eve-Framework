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
	protected $method;

	protected $body;
	protected $query;
	protected $fragment;
	protected $module;
	protected $controller;
	protected $action;
	protected $baseUri;
	protected $uri;
	protected $params;
	protected $language;
	protected $exception;
	protected $dispatched;
	protected $routed;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config)
	{
		parent::__construct($config);

		$this->body = @file_get_contents('php://input');
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

			// Use ORIG_PATH_INFO
			if (isset($_SERVER['ORIG_PATH_INFO']) && null !== $_SERVER['ORIG_PATH_INFO'])
			{
				$uri = $_SERVER['ORIG_PATH_INFO'];
				break;
			}
		} while (0);

		// Set the uri and base uri
		if (null !== $uri) {
			$this->uri = preg_replace(self::URI_REGEX, '', parse_url($uri, PHP_URL_PATH));
			$parsedUri = parse_url($uri);
			$this->query = (isset($parsedUri['query'])) ? $parsedUri['query'] : null;
			$this->fragment = (isset($parsedUri['fragment'])) ? $parsedUri['fragment'] : null;
			$this->setBaseUri($this->uri);
		}
	}

	/**
	 * Get HTTP request body
	 *
	 * @return string|false
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Set the query string
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setQuery($value = null)
	{
		if (null !== $value && is_string($value)) {
			$this->query = $value;
		}
		return $this;
	}

	/**
	 * Get the query string
	 *
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->query;
	}

	/**
	 * Set the fragment string
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setFragment($value = null)
	{
		if (null !== $value && is_string($value)) {
			$this->fragment = $value;
		}
		return $this;
	}

	/**
	 * Get the fragment string
	 *
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
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
	protected function DEPRECATED_setBaseUri($value)
	{
		$scriptFilename = (isset($_SERVER['SCRIPT_FILENAME'])) ? $_SERVER['SCRIPT_FILENAME'] : null;
		$ext = 'php';

		do {
			if (!is_string($scriptFilename)) {
				break;
			}

			$scriptFilename = basename($scriptFilename, $ext);
			$scriptName = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : null;

			if (is_string($scriptName)) {
				$scriptFilename = basename($scriptName);
			}
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
		if (null !== $value) {
			$this->module = (string) $value;
		}
		return $this;
	}

	/**
	 * Get the module name
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Set the controller name
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setController($value = null)
	{
		if (null !== $value) {
			$this->controller = (string) $value;
		}
		return $this;
	}

	/**
	 * Get the controller name
	 *
	 * @return string
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Set the action name
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setAction($value = null)
	{
		if (null !== $value) {
			$this->action = (string) $value;
		}
		return $this;
	}

	/**
	 * Get the action name
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Set the base uri
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setBaseUri($value = null)
	{
		if (null !== $value && strlen($value)) {
			$this->baseUri = (string) $value;
		}
		return $this;
	}

	/**
	 * Get the base uri
	 *
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->baseUri;
	}

	/**
	 * Set the request uri
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setUri($value = null)
	{
		if (null !== $value) {
			$this->uri = (string) $value;
		}
		return $this;
	}

	/**
	 * Get or get the request uri
	 *
	 * @return string
	 */
	public function getUri()
	{
		return $this->uri;
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
		if (null !== $value) {
			$this->params[$key] = (string) $value;
		}
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
		return (isset($this->params[$key])) ? $this->params[$key] : $default;
	}

	/**
	 * Set dispatched flag
	 *
	 * @param bool $value
	 * @return Request
	 */
	public function setDispatched($value)
	{
		$this->dispatched = (bool) $value;
		return $this;
	}

	/**
	 * Get dispatched flag
	 *
	 * @return bool
	 */
	public function isDispatched()
	{
		return (bool) $this->dispatched;
	}

	/**
	 * Set routed flag
	 *
	 * @param bool $value
	 * @return Request
	 */
	public function setRouted($value)
	{
		$this->routed = (bool) $value;
		return $this;
	}

	/**
	 * Get routed flag
	 *
	 * @return bool
	 */
	public function isRouted()
	{
		return (bool) $this->routed;
	}

	/**
	 * Set the exception object
	 *
	 * @param \Exception $exception
	 * @return Request
	 */
	public function setException(\Exception $exception = null)
	{
		if (null !== $exception) { $this->exception = $exception; }
		return $this;
	}

	/**
	 * Get the exception object
	 *
	 * @return \Exception
	 */
	public function getException()
	{
		return (is_object($this->exception)) ? $this->exception : null;
	}

	/**
	 * Set parameters array
	 *
	 * @param array $value
	 * @return array
	 */
	public function setParams($value = null)
	{
		if (null !== $value && is_array($value)) {
			$this->params = $value;
		}
		return $this;
	}

	/**
	 * Get parameters array
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Set language
	 *
	 * @param string $value
	 * @return Request
	 */
	public function setLanguage($value = null)
	{
		if (null !== $value) {
			$this->language = (string) $value;
		}
		return $this;
	}

	/**
	 * Get language
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return (string) $this->language;
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
		if (null === $this->method) {
			$this->method = new Request\Method();
		}
		return $this->method;
	}
}
