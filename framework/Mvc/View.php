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

class View implements \Eve\ResourceInterface
{
	/**
	 * Path to views directory
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $module;

	/**
	 * Controller name
	 *
	 * @var string
	 */
	protected $controller;

	/**
	 * View layout file
	 *
	 * @var string
	 */
	 protected $layout = 'default';

	/**
	 * View script file for action
	 *
	 * @var string
	 */
	protected $view = 'index';

	/**
	 * Page data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Returns a new view object for the given view.
	 *
	 * @param string $path the path to views directory
	 * @param string $layout the layout view file
	 * @param string $view the view file
	 */
	public function __construct(Request $request = null, $layout = null)
	{
		// Get "Module" name and full path to the module directory
		$this->setModule(ucfirst($request->getModule()));
		$this->setController($request->getController());
		$this->setView($request->getController() . '/' . $request->getAction());
		$this->setPath(\Eve::app()->getComponent('config')->get('modulesPath'));

		null !== $layout && $this->setLayout($layout);
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
		if (isset($this->data[$key])) { return $this->data[$key]; }
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
		$this->data[$key] = $value;
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
		return isset($this->data[$key]);
	}

	/**
	 * Magic Method for removing an item from the view data.
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		unset($this->data[$key]);
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
			$this->data[$key] = $value;
		} else {
			$this->data = array_merge($this->data, $key);
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
		if (isset($this->data[$key])) { return $this->data[$key]; }
		$default = null;
		return $default;
	}

	/**
	 * Set module name
	 *
	 * @param string $value
	 * @return View
	 */
	public function setModule($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}
		$this->module = (string) $value;
		return $this;
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function getModule()
	{
		return (string) $this->module;
	}

	/**
	 * Set controller name
	 *
	 * @param string $value
	 * @return View
	 */
	public function setController($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}
		$this->controller = (string) $value;
		return $this;
	}

	/**
	 * Get controller name
	 *
	 * @return string
	 */
	public function getController()
	{
		return (string) $this->controller;
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

		// Detect module definition
		$path = (($pos = strpos($value, ':')) !== false) ? $this->getPath(substr($value, 0, $pos)) : $this->getPath();
		$this->view = $path . '/views/' . substr($value, $pos) . '.php';

		return $this;
	}

	/**
	 * Get view file
	 *
	 * @return string
	 */
	public function getView()
	{
		return (string) $this->view;
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
		$this->path = (string) $value;
		return $this;
	}

	/**
	 * Getter for the view path
	 * Passing a module value will return a path based on that module name, otherwise the view module value is used
	 *
	 * @param string $module
	 * @return string
	 */
	public function getPath($module = null)
	{
		return (string) $this->path . '/' . (null !== $module ? $module : $this->module) . '/views'
	}

	/**
	 * Setter for the view layout.
	 * You may pass a format of module:path/to/layout in addition to just layout or sub/layout
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function setLayout($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}

		// Detect module definition
		$path = (($pos = strpos($value, ':')) !== false) ? $this->getPath(substr($value, 0, $pos)) : $this->getPath();
		$this->layout = $path . '/layouts/' . substr($value, $pos) . '.php';

		return $this;
	}

	/**
	 * Getter for the view layout
	 *
	 * @return string
	 */
	public function getLayout()
	{
		return (string) $this->layout;
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
		// Set view script if one was passed
		if (null !== $view) {
			$this->setView($view);
		}

		if (null !== $data && is_array($data)) {
			$this->data = $replace === false ? array_merge($this->data, $data) : $data;
		}

		// Catchy any exceptions/errors that happen inside a view
		try {
			// Extract data to local variables
			extract($this->data);

			// Throw exception if view file not resolved
			if ($viewAbsolutePath = stream_resolve_includepath($this->view)) {
				// Start output buffering for content
				ob_start();

				// Include view script
				include $viewAbsolutePath;

				// Assign output buffer from view to $content
				$content = ob_get_clean();
			}

			// Throw exception if layout file not resolved
			if (!$layoutAbsolutePath = stream_resolve_includepath($this->layout)) {
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
		$view = $this->getPath() . '/partials/' . $viewScript . '.php';

		// Catch any exceptions/errors that happen inside a view
		try {
			// Render page
			if (!$resourceAbsolutePath = stream_resolve_includepath($view)) {
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
