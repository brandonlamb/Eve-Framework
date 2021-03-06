<?php
namespace Eve\Mvc;

class Route
{
	/**
	 * @var string URL of this route
	 */
	protected $url;

	/**
	 * @var array Accepted HTTP methods for this route
	 */
	protected $methods = ['GET', 'POST', 'PUT', 'DELETE'];

	/**
	 * @var mixed target for this route, can be anything
	 */
	protected $target;

	/**
	 * @var string The name of this route, used for reverse routing
	 */
	protected $name;

	/**
	 * @var array Custom parameter filters for this route
	 */
	protected $filters = [];

	/**
	 * @var array Array containing parameters passed through request URL
	 */
	protected $parameters = [];

	/**
	 * Get the url for this route
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set the url
	 * @param string $url
	 * @return Route
	 */
	public function setUrl($url)
	{
		// make sure that the URL is NOT suffixed with a forward slash
		$this->url = rtrim((string) $url, '/');
		return $this;
	}

	/**
	 * Gets the target
	 * @return mixed
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Set the target
	 * @param mixed $target
	 * @return Route
	 */
	public function setTarget($target)
	{
		$this->target = $target;
		return $this;
	}

	/**
	 * Get array of methods
	 * @return array
	 */
	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * set array of methods
	 * @param array $methods
	 * @return Route
	 */
	public function setMethods(array $methods)
	{
		$this->methods = $methods;
		return $this;
	}

	/**
	 * Get the route name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the route name
	 * @param string $name
	 * @return Route
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}

	/**
	 * Get the filters
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * Set the array of filters
	 * @param array $filters
	 * @return Route
	 */
	public function setFilters(array $filters)
	{
		$this->filters = (array) $filters;
		return $this;
	}

	/**
	 * Return the regex string of the route, replacing named placeholders with \w+
	 * @return string
	 */
	public function getRegex()
	{
		return preg_replace_callback('/:(\w+)/', [&$this, 'substituteFilter'], $this->url);
	}

	/**
	 * @return string
	 */
	protected function substituteFilter($matches)
	{
		if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
			return $this->filters[$matches[1]];
		}

		return '([\w-]+)';
	}

	/**
	 * Get array of parameters
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * set array of parameters
	 * @param array $parameters
	 * @return Route
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = (array) $parameters;
		return $this;
	}

	/**
	 * Fetch a parameter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getParameter($key)
	{
		return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
	}
}
