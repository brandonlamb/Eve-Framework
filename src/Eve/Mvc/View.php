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
use Eve\DI\InjectionAwareInterface;
use Eve\Events\EventsAwareInterface;

class View implements InjectionAwareInterface, EventsAwareInterface
{
	use \Eve\DI\InjectableTrait;

	const LEVEL_MAIN_LAYOUT		= 1;
	const LEVEL_AFTER_TEMPLATE  = 2;
	const LEVEL_LAYOUT			= 3;
	const LEVEL_BEFORE_TEMPLATE	= 4;
	const LEVEL_ACTION_VIEW		= 5;
	const LEVEL_NO_RENDER		= 6;

	/**
	 * Render level
	 * @var int
	 */
	protected $renderLevel = 1;

	/**
	 * Views directory
	 * @var string
	 */
	protected $viewsDir;

	/**
	 * Layouts directory
	 * @var string
	 */
	protected $layoutsDir;

	/**
	 * Partials directory
	 * @var string
	 */
	protected $partialsDir;

	/**
	 * Main view
	 * @var string
	 */
	protected $mainView;

	/**
	 * View variables
	 * @var array
	 */
	protected $viewVars = array();

	/**
	 * Rendered view content
	 * @var string
	 */
	protected $content;

	/**
	 * Resets the view to its factory default values
	 * @return View
	 */
	public function reset()
	{
		$this->renderLevel 		= 1;
		$this->viewsDir 		= null;
		$this->layoutsDir		= null;
		$this->partialsDir		= null;
		$this->mainView 		= null;
		$this->viewVars			= array();
		$this->content 			= null;

		return $this;
	}

	/**
	 * Set the render level
	 * @param int $level
	 * @return View
	 */
	public function setRenderLevel($level)
	{
		$level = (int) $level;

		switch ($level) {
			case static::LEVEL_MAIN_LAYOUT:
			case static::LEVEL_AFTER_LAYOUT:
			case static::LEVEL_LAYOUT:
			case static::LEVEL_BEFORE_TEMPLATE:
			case static::LEVEL_ACTION_VIEW:
			case static::LEVEL_NO_RENDER:
				$this->renderLevel = $level;
			default:
				throw new \InvalidArgumentException('Invalid render level');
		}

		return $this;
	}

	/**
	 * Set the views directory
	 * @param string $dir
	 * @return View
	 */
	public function setViewsDir($directory)
	{
		if (is_string($directory)) {
			$this->viewsDir = (string) $directory;
		}
		return $this;
	}

	/**
	 * Get the views directory
	 * @return string
	 */
	public function getViewsDir()
	{
		return $this->viewsDir;
	}

	/**
	 * Set the layouts directory
	 * @param string $dir
	 * @return View
	 */
	public function setLayoutsDir($directory)
	{
		if (is_string($directory)) {
			$this->layoutsDir = (string) $directory;
		}
		return $this;
	}

	/**
	 * Get the layouts directory
	 * @return string
	 */
	public function getLayoutsDir()
	{
		return $this->layoutsDir;
	}

	/**
	 * Set the partials directory
	 * @param string $dir
	 * @return View
	 */
	public function setPartialsDir($directory)
	{
		if (is_string($directory)) {
			$this->partialsDir = (string) $directory;
		}
		return $this;
	}

	/**
	 * Get the partials directory
	 * @return string
	 */
	public function getPartialsDir()
	{
		return $this->partialsDir;
	}

	/**
	 * Sets the main view
	 * @param string $view
	 * @return View
	 */
	public function setMainView($view)
	{
		if (is_string($view)) {
			$this->mainView = (string) $view;
		}
		return $this;
	}

	/**
	 * Gets the main view
	 * @return string
	 */
	public function getMainView()
	{
		return $this->mainView;
	}

	/**
	 * Sets all view variabls at once
	 * @param array $values
	 * @return View
	 */
	public function setVars(array $values)
	{
		$this->viewVars = $values;
		return $this;
	}

	/**
	 * Sets a view variable
	 * @param string $key
	 * @param mixed $value
	 * @return View
	 */
	public function setVar($key, $value)
	{
		if (is_string($key)) {
			$this->viewVars[$key] = $view;
		}
		return $this;
	}

	/**
	 * Gets a view variable by key
	 * @param string $key
	 * @return mixed
	 */
	public function &getVar($key)
	{
		$default = null;
		return array_key_exists($key, $this->viewVars) ? $this->viewVars[$key] : $default;
	}

	/**
	 * Sets the view content
	 * @param string $content
	 * @return View
	 */
	public function setContent($content)
	{
		if (is_string($content)) {
			$this->content = (string) $content;
		}
		return $this;
	}

	/**
	 * Gets the view content
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}














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
	 * @param Request $request
	 */
	public function __construct($path, Request $request = null)
	{
		// Set path from config
		$this->setPath($path);

		// If no request object was passed then just return
		if (null === $request) {
			return;
		}

		// Save reference to Request object
		$this->request = $request;

		// Set module and view from request object
		$this->setModule($request->getModule());
		$this->setView($request->getAction());
	}

	/**
	 * Returns a property value. Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <pre>
	 * $value = $component->propertyName;
	 * </pre>
	 *
	 * @param  string $key the property name
	 * @return mixed  the property value
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
	 * @param  string $key   the property name or the event name
	 * @param  mixed  $value the property value or callback
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
	 * @param  string  $key the property name or the event name
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
	 * @param  string|array $key
	 * @param  mixed		$value
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
	 * The first letter of the module name will be capitalized to conform to namespace format
	 *
	 * @param  string $value
	 * @return View
	 */
	public function setModule($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}
		$this->module = (string) ucwords($value);

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
	 * Set view file
	 * Accepts multiple formats to resolve module/controller/view paths.
	 * Two leading slashes denote specifying a full module/controller path:
	 *   //module/controller/view
	 * You can also specify sub directories for the view inside a controller directory:
	 *   //module/controller/something/view
	 * To specify a view in the current module, but different controller, use a single leading slash:
	 *   /controller/view
	 * No leading slashes assumes current module, current controller and relative view path:
	 *   view
	 *   sub/view
	 *
	 * @param  string $value
	 * @return View
	 */
	public function setView($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}

		// $value contains no '/', just use defaults
		if ($value{0} !== '/') {
			$this->view = $this->getPath() . static::PATH_VIEWS . $this->request->getController() . '/' . $value . '.php';

			return $this;
		}

		// Split $value into an array
		$parts = explode('/', trim($value, '/'));
		$numParts = count($parts);

		// Verify we have the minimum number required to parsed
		if (($value{1} === '/' && $numParts < 3) || $numParts < 2) {
			throw new Exception('Invalid view path ' . $value);
		}

		// Detect module definition
		if ($value{1} === '/') {
			$this->setModule(array_shift($parts));
		}

		// Set the view by re-combining parts
		$this->view = $this->getPath() . static::PATH_VIEWS . implode('/', $parts) . '.php' . "\n";

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
	 * @param  string $value
	 * @return View
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
	 * @param  string $module
	 * @return string
	 */
	public function getPath($module = null)
	{
		return (string) $this->path . '/' . (null !== $module ? ucfirst($module) : $this->module);
	}

	/**
	 * Setter for the view layout.
	 * You may pass a format of //module/path/to/layout in addition to just layout or sub/layout
	 *
	 * @see View::setView
	 * @param  string $value
	 * @return View
	 */
	public function setLayout($value)
	{
		if (!is_string($value)) {
			throw new Exception(__METHOD__ . ' expects a string');
		}

		// $value contains no '/', just use defaults
		if ($value{0} !== '/') {
			$this->layout = $this->getPath() . static::PATH_LAYOUTS . $value . '.php';

			return $this;
		}

		// Split $value into an array
		$parts = explode('/', trim($value, '/'));
		$numParts = count($parts);

		// Verify we have the minimum number required to parsed
		if ($numParts < 2) {
			throw new Exception('Invalid layout path ' . $value);
		}

		// Detect module definition
		$module = ($value{1} === '/') ? array_shift($parts) : $this->getModule();

		// Set the view by re-combining parts
		$this->layout = $this->getPath($module) . static::PATH_LAYOUTS . implode('/', $parts) . '.php' . "\n";

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
	 *
	 */
	protected function getPartial($value)
	{
		// $value contains no '/', just use defaults
		if ($value{0} !== '/') {
			return $this->getPath() . static::PATH_PARTIALS . $value . '.php';
		}

		// Split $value into an array
		$parts = explode('/', trim($value, '/'));
		$numParts = count($parts);

		// Verify we have the minimum number required to parsed
		if ($numParts < 2) {
			throw new Exception('Invalid layout path ' . $value);
		}

		// Detect module definition
		$module = ($value{1} === '/') ? array_shift($parts) : $this->getModule();

		// Set the view by re-combining parts
		return $this->getPath($module) . static::PATH_PARTIALS . implode('/', $parts) . '.php' . "\n";
	}

	/**
	 * Render the page and layout
	 *
	 * @param  string $view,   view file to use
	 * @param  array  $data	view template data
	 * @param  bool   $replace whether to replace view data
	 * @return string
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function render($view = null, $data = null, $replace = false)
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
			if ($viewAbsolutePath = stream_resolve_include_path($this->view)) {
				// Start output buffering for content
				ob_start();

				// Include view script
				include $viewAbsolutePath;

				// Assign output buffer from view to $content
				$content = ob_get_clean();
			}

			// Throw exception if layout file not resolved
			if (!$layoutAbsolutePath = stream_resolve_include_path($this->layout)) {
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
	 * @param  string $view, the partial view file
	 * @param  array  $data, local array of view variables
	 * @return string
	 */
	public function partial($view, array $data = array())
	{
		$view = $this->getpartial($view);

		// Catch any exceptions/errors that happen inside a view
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
