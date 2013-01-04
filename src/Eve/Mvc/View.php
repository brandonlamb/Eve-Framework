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

	const LEVEL_NO_RENDER		= 0;
	const LEVEL_ACTION_VIEW		= 1;
	const LEVEL_BEFORE_TEMPLATE	= 2;
	const LEVEL_LAYOUT			= 3;
	const LEVEL_AFTER_TEMPLATE  = 4;
	const LEVEL_MAIN_LAYOUT		= 5;

	/**
	 * Rendering started flag
	 * @var bool
	 */
	protected $renderStart = false;

	/**
	 * Render level
	 * @var int
	 */
	protected $renderLevel = 1;

	/**
	 * Disabled render levels
	 * @var array
	 */
	protected $disableLevel = array();

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
		$this->disableLevel 	= array();
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
		if (array_key_exists($key, $this->viewVars)) {
			return $this->viewVars[$key];
		}
		$default = null;
		return $default;
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
	 * Checks if a property value is null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using isset() to detect if a component property is set or not.
	 * @param  string  $key the property name or the event name
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->viewVars[$key]);
	}

	/**
	 * Magic Method for removing an item from the view data.
	 * @param string $key
	 */
	public function __unset($key)
	{
		unset($this->viewVars[$key]);
	}

	/**
	 * Return the view's HTML
	 * @return string
	 */
	public function __toString()
	{
		return $this->getContent();
	}

	/**
	 * Starts rendering process enabling the output buffer
	 */
	public function start()
	{
		if ($this->renderStart !== false) {
			return;
		}

		ob_start();
	}

	/**
	 * Finish rendering process disabling the output buffer
	 */
	public function finish()
	{
		if ($this->renderStart !== false) {
			return;
		}

		// Assign output buffer from view to $content
		$this->content = ob_get_clean();

		while (ob_get_level() > 1) {
			ob_get_clean();
		}
	}




	/**
	 * Executes render process from dispatching data
	 *
	 * @param string $controllerName
	 * @param string $actionName
	 * @param array $params
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function render($controllerName, $actionName, array $params = array())
	{
		// Dont render if level is set to no render or we have not started rendering
		if ($this->renderLevel === static::LEVEL_NO_RENDER || $this->renderStart === false) {
			return;
		}

		// Merge parameters with viewVars if data is passed
		if (count(params) > 0) {
			$this->viewVars = array_merge($this->viewVars, $params);
		}

		// Catchy any exceptions/errors that happen inside a view
		try {
			// Extract data to local variables
			extract($this->data);

			// Throw exception if view file not resolved
			if ($viewAbsolutePath = stream_resolve_include_path($this->view)) {
				// Include view script
				include $viewAbsolutePath;


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
	 * @param string $partialPath, the partial view file
	 * @return string
	 */
	public function partial($partialPath)
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
