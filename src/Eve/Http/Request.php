<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @package Eve\Request
 */
namespace Eve\Http;

use Eve\Di\InjectionAwareInterface,
	Eve\Di\InjectableTrait,
	Eve\Events\EventsAwareInterface;

class Request implements InjectionAwareInterface, EventsAwareInterface
{
	use InjectableTrait;

	const SCHEME_HTTP		= 'http';
	const SCHEME_HTTPS		= 'https';
	const URI_REGEX			= '/[^a-zA-Z0-9_\-\/\s]/';
	const METHOD_HEAD       = 'HEAD';
	const METHOD_GET        = 'GET';
	const METHOD_POST       = 'POST';
	const METHOD_PUT        = 'PUT';
	const METHOD_DELETE     = 'DELETE';
	const METHOD_OPTIONS    = 'OPTIONS';
	const METHOD_OVERRIDE   = '_METHOD';

	protected $method;
	protected $body;
	protected $query;
	protected $fragment;
	protected $baseUri;
	protected $uri;
	protected $params;
	protected $language;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$uri = null;

		do {
			// Use HTTP_X_REWRITE_URL
			if (isset($_SERVER['HTTP_X_REWRITE_URL']) && null !== $_SERVER['HTTP_X_REWRITE_URL']) {
				$uri = $_SERVER['HTTP_X_REWRITE_URL'];
				break;
			}

			// Use IIS_WasUrlRewritten
			if (isset($_SERVER['IIS_WasUrlRewritten']) && null !== $_SERVER['IIS_WasUrlRewritten']) {
				$uri = $_SERVER['IIS_WasUrlRewritten'];
				break;
			}

			// Use REQUEST_URI
			if (isset($_SERVER['REQUEST_URI']) && null !== $_SERVER['REQUEST_URI']) {
				$uri = $_SERVER['REQUEST_URI'];
				break;
			}

			// Use PATH_INFO
			if (isset($_SERVER['PATH_INFO']) && null !== $_SERVER['PATH_INFO']) {
				$uri = $_SERVER['PATH_INFO'];
				break;
			}

			// Use ORIG_PATH_INFO
			if (isset($_SERVER['ORIG_PATH_INFO']) && null !== $_SERVER['ORIG_PATH_INFO']) {
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

		isset($_SERVER['REQUEST_METHOD']) && $this->setMethod($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Get HTTP request body
	 *
	 * @return string|false
	 */
	public function getBody()
	{
		if (null === $this->body) {
			$this->body = @file_get_contents('php://input');
		}
		return $this->body;
	}

	/**
	 * Set the query string
	 *
	 * @param  string  $value
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
	 * @param  string  $value
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
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function getCookie($key, $default = null)
	{
		return isset($_COOKIES[$key]) ? \Eve\Filter::xss($_COOKIES[$key]) : $default;
	}

	/**
	 * Return a file key
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return array
	 */
	public function getFile($key, $default = null)
	{
		return (is_array($_FILES) && isset($_FILES[$key])) ? \Eve\Filter::xss($_FILES[$key]) : $default;
	}

	/**
	 * Checks whether $_FILES superglobal has files
	 * @return bool
	 */
	public function hasFiles()
	{
		return count($_FILES) > 0 ? true : false;
	}

	/**
	 * Return a post key
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function getPost($key, $default = null)
	{
		return isset($_POST[$key]) ? \Eve\Filter::xss($_POST[$key]) : $default;
	}

	/**
	 * Checks whether $_POST superglobal has certain index
	 * @param string $key
	 * @return bool
	 */
	public function hasPost($key)
	{
		return isset($_POST[$key]);
	}

	/**
	 * Return a get query key
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function getQuery($key, $default = null)
	{
		return isset($_GET[$key]) ? \Eve\Filter::xss($_GET[$key]) : $default;
	}

	/**
	 * Checks whether $_GET superglobal has certain index
	 * @param string $key
	 * @return bool
	 */
	public function hasQuery($key)
	{
		return isset($_GET[$key]);
	}

	/**
	 * Return a request key
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function getRequest($key, $default = null)
	{
		return isset($_REQUEST[$key]) ? \Eve\Filter::xss($_REQUEST[$key]) : $default;
	}

	/**
	 * Set the base uri
	 *
	 * @param  string  $value
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
	 * @param  string  $value
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
	 * @param  string  $key
	 * @param  string  $value
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
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function getParam($key, $default = null)
	{
		return (isset($this->params[$key])) ? $this->params[$key] : $default;
	}

	/**
	 * Set parameters array
	 *
	 * @param  array $value
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
	 * @param  string  $value
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
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function getServer($key, $default = null)
	{
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}

	/**
	 * Checks whether $_SERVER superglobal has certain index
	 *
	 * @param string $key
	 * @return bool
	 */
	public function hasServer($key)
	{
		return isset($_SERVER[$key]);
	}

	/**
	 * Creates a Request\Method object and returns it for chaining isGet() type methods
	 *
	 * @return Request\Method
	 */
	public function getMethod()
	{
		// Intentionally fall-through until the final case to set the value.
		// Default is to throw exception on invalid method type.
		switch ($this->method) {
			case static::METHOD_HEAD:
			case static::METHOD_GET:
			case static::METHOD_POST:
			case static::METHOD_PUT:
			case static::METHOD_DELETE:
			case static::METHOD_OPTIONS:
			case static::METHOD_OVERRIDE:
				return $this->method;
				break;
			default:
				throw new \InvalidArgument('Invalid method type ' . $value);
		}
	}

   /**
	 * Manually override/set the method
	 *
	 * @param  string $value
	 * @return Method
	 */
	public function setMethod($value)
	{
		// Intentionally fall-through until the final case to set the value.
		// Default is to throw exception on invalid method type.
		switch (strtoupper($value)) {
			case static::METHOD_HEAD:
			case static::METHOD_GET:
			case static::METHOD_POST:
			case static::METHOD_PUT:
			case static::METHOD_DELETE:
			case static::METHOD_OPTIONS:
			case static::METHOD_OVERRIDE:
				$this->method = strtoupper($value);
				break;
			default:
				throw new \InvalidArgument('Invalid method type ' . $value);
		}
	}

	/**
	 * Is this a GET request?
	 *
	 * @return bool
	 */
	public function isGet()
	{
		return $this->method === self::METHOD_GET;
	}

	/**
	 * Is this a POST request?
	 *
	 * @return bool
	 */
	public function isPost()
	{
		return $this->method === self::METHOD_POST;
	}

	/**
	 * Is this a PUT request?
	 *
	 * @return bool
	 */
	public function isPut()
	{
		return $this->method === self::METHOD_PUT;
	}

	/**
	 * Is this a DELETE request?
	 *
	 * @return bool
	 */
	public function isDelete()
	{
		return $this->method === self::METHOD_DELETE;
	}

	/**
	 * Is this a HEAD request?
	 *
	 * @return bool
	 */
	public function isHead()
	{
		return $this->method === self::METHOD_HEAD;
	}

	/**
	 * Is this a OPTIONS request?
	 *
	 * @return bool
	 */
	public function isOptions()
	{
		return $this->method === self::METHOD_OPTIONS;
	}

	/**
	 * Is this a XHR request?
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		$requestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : (isset($_SERVER['X_REQUESTED_WITH']) ? $_SERVER['X_REQUESTED_WITH'] : null);
		return ($this->getParam('isajax') === true || (isset($requestedWith) && $requestedWith === 'XMLHttpRequest'));
	}
}
