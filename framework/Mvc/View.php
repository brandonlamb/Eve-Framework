<?php
/**
 * Eve Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package Eve
 * @version 0.1.0
 */
namespace Eve\Mvc;

class View
{
	/**
	 * Path to views directory
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * View layout file
	 *
	 * @var string
	 */
	 protected $_layout = 'default';

	/**
	 * View script file for action
	 *
	 * @var string
	 */
	protected $_view = 'index';

	/**
	 * Page data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @const string
	 */
	const RES_CONFIG = 'config';

	/**
	 * Returns a new view object for the given view.
	 *
	 * @param string $path the path to views directory
	 * @param string $layout the layout view file
	 * @param string $view the view file
	 */
	public function __construct($path = null, $layout = null, $view = null)
	{
		null !== $path && $this->setPath($path);
		null !== $layout && $this->setLayout($layout);
		null !== $view && $this->setView($view);
	}

	/**
	 * Returns a property value. Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <pre>
	 * $value = $component->propertyName;
	 * </pre>
	 *
	 * @param string $key the property name
	 * @return mixed the property value
	 * @see __set
	 */
	public function &__get($key)
	{
		if (isset($this->_data[$key])) { return $this->_data[$key]; }
		$default = null;
		return $default;
	}

	/**
	 * Sets value of a component property.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attach an event handler
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->eventName=$callback;
	 * </pre>
	 *
	 * @param string $key the property name or the event name
	 * @param mixed $value the property value or callback
	 * @return mixed
	 * @see __get
	 */
	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * Checks if a property value is null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using isset() to detect if a component property is set or not.
	 *
	 * @param string $key the property name or the event name
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->_data[$key]);
	}

	/**
	 * Magic Method for removing an item from the view data.
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		unset($this->_data[$key]);
	}

	/**
	 * Return the view's HTML
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Set a value
	 *
	 * @param string|array $key
	 * @param mixed $value
	 * @return View
	 */
	public function set($key, $value = null)
	{
		if (!is_array($key)) {
			$this->_data[$key] = $value;
		} else {
			$this->_data = array_merge($this->_data, $key);
		}
		return $this;
	}

	/**
	 * Get a value
	 *
	 * @return mixed;
	 */
	public function &get($key)
	{
		if (isset($this->_data[$key])) { return $this->_data[$key]; }
		$default = null;
		return $default;
	}

	/**
	 * Set view file
	 *
	 * @param string $value
	 * @return View
	 */
	public function setView($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}
		$this->_view = (string) $value;
		return $this;
	}

	/**
	 * Get view file
	 *
	 * @return string
	 */
	public function getView()
	{
		return (string) $this->_view;
	}

	/**
	 * Setter for the view path
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function setPath($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}
		$this->_path = (string) $value;
		return $this;
	}

	/**
	 * Getter for the view path
	 *
	 * @return string
	 */
	public function getPath()
	{
		return (string) $this->_path;
	}

	/**
	 * Setter for the view layout
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function setLayout($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}
		$this->_layout = (string) $value;
		return $this;
	}

	/**
	 * Getter for the view layout
	 *
	 * @return string
	 */
	public function getLayout()
	{
		return (string) $this->_layout;
	}

	/**
	 * Render the page and layout
	 *
	 * @param string $viewScript, view file to use
	 * @param array $data view template data
	 * @param bool $replace whether to replace view data
	 * @return string
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function render($viewScript = null, $data = null, $replace = false)
	{
		$view = null === $viewScript ? $this->_view : $viewScript;
		$view = $this->_path . '/' . $view . '.php';
		$layout = $this->_path . '/layouts/' . $this->_layout . '.php';

		if (null !== $data && is_array($data)) {
			$this->_data = $replace === false ? array_merge($this->_data, $data) : $data;
		}

		// Catchy any exceptions/errors that happen inside a view
		try {
			// Extract data to local variables
			extract($this->_data);

			// Throw exception if view file not resolved
			if ($viewAbsolutePath = stream_resolve_include_path($view)) {
				// Start output buffering for content
				ob_start();

				// Include view script
				include $viewAbsolutePath;

				// Assign output buffer from view to $content
				$content = ob_get_clean();
			}

			// Throw exception if layout file not resolved
			if (!$layoutAbsolutePath = stream_resolve_include_path($layout)) {
				throw new \RuntimeException('Layout file ' . $layoutAbsolutePath . ' not found');
			}

			// Start output buffering for layout
			ob_start();

			// Include the layout view script
			include $layoutAbsolutePath;

			// Return the layout + view output buffer
			return ob_get_clean();
		} catch (\Exception $e) {
			while (ob_get_level() > 1) { ob_get_clean(); }
			throw $e;
		}
	}

	/**
	 * Render a partial view
	 *
	 * @param string $viewScript, the partial view file
	 * @param array $data, local array of view variables
	 * @return string
	 */
	public function partial($viewScript, array $data = array())
	{
		$view = $this->_path . '/partials/' . $viewScript . '.php';

		// Catchy any exceptions/errors that happen inside a view
		try {
			// Render page
			if (!$resourceAbsolutePath = stream_resolve_include_path($view)) {
				throw new \RuntimeException('View file ' . $view . ' not found');
			}

			ob_start();
			extract($data);
			include $resourceAbsolutePath;
			return ob_get_clean();
		} catch (\Exception $e) {
			while (ob_get_level() > 1) { ob_get_clean(); }
			throw $e;
		}
	}
}
