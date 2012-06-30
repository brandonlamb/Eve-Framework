<?php
namespace Eve\Mvc\Request;

class Method
{
	const METHOD_HEAD		= 'HEAD';
	const METHOD_GET		= 'GET';
	const METHOD_POST		= 'POST';
	const METHOD_PUT		= 'PUT';
	const METHOD_DELETE		= 'DELETE';
	const METHOD_OPTIONS	= 'OPTIONS';
	const METHOD_OVERRIDE	= '_METHOD';

	/**
	 * @var string, the method type
	 */
	protected $_method;

	/**
	 * Method Constructor
	 */
	public function __construct()
	{
		$this->_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;
	}

	/**
	 * Manually override/set the method
	 *
	 * @param string $value
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
				$this->_method = $value;
				break;
			default:
				throw new \Eve\Mvc\RequestException('Invalid method type ' . $value);
		}
	}

	/**
	 * Is this a GET request?
	 *
	 * @return bool
	 */
	public function isGet()
	{
		return $this->_method === self::METHOD_GET;
	}

	/**
	 * Is this a POST request?
	 *
	 * @return bool
	 */
	public function isPost()
	{
		return $this->_method === self::METHOD_POST;
	}

	/**
	 * Is this a PUT request?
	 *
	 * @return bool
	 */
	public function isPut()
	{
		return $this->_method === self::METHOD_PUT;
	}

	/**
	 * Is this a DELETE request?
	 *
	 * @return bool
	 */
	public function isDelete()
	{
		return $this->_method === self::METHOD_DELETE;
	}

	/**
	 * Is this a HEAD request?
	 *
	 * @return bool
	 */
	public function isHead()
	{
		return $this->_method === self::METHOD_HEAD;
	}

	/**
	 * Is this a OPTIONS request?
	 *
	 * @return bool
	 */
	public function isOptions()
	{
		return $this->_method === self::METHOD_OPTIONS;
	}

	/**
	 * Is this a XHR request?
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		return ($this->getParam('isajax') === true || (isset($_SERVER['X_REQUESTED_WITH']) && $_SERVER['X_REQUESTED_WITH'] === 'XMLHttpRequest'));
	}

	/**
	 * Is this a XHR request?
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest()
	{
		return $this->isAjax();
	}
}
