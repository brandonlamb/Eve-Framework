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
	 * Render level, default 5 (max)
	 * @var int
	 */
	protected $renderLevel = 5;

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
	protected $mainView = 'index';

	/**
	 * Template before view
	 *
	 * Rendered before the action view
	 * @var array
	 */
	protected $templateBeforeView = array();

	/**
	 * Template after view
	 *
	 * Rendered after the action view
	 * @var array
	 */
	protected $templateAfterView = array();

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
	 * Action name
	 * @var string
	 */
	protected $actionName;

	/**
	 * Controller name
	 * @var string
	 */
	protected $controllerName;

	/**
	 * Picked view (0 => controller, 1 => action)
	 * @var array
	 */
	protected $pickView;

	/**
	 * View file suffix
	 * @var string
	 */
	protected $viewSuffix = '.phtml';

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
			case static::LEVEL_AFTER_TEMPLATE:
			case static::LEVEL_LAYOUT:
			case static::LEVEL_BEFORE_TEMPLATE:
			case static::LEVEL_ACTION_VIEW:
			case static::LEVEL_NO_RENDER:
				$this->renderLevel = $level;
				break;
			default:
				throw new \InvalidArgumentException('Invalid render level ' . $break);
		}

		return $this;
	}

	/**
	 * Disable one or more render levels
	 * @param int|array $levels
	 * @return View
	 */
	public function disableLevel($levels)
	{
		if (is_numeric($levels)) {
			$levels = array($levels);
		}

		foreach ($levels as $level) {
			switch ($level) {
				case static::LEVEL_MAIN_LAYOUT:
				case static::LEVEL_AFTER_TEMPLATE:
				case static::LEVEL_LAYOUT:
				case static::LEVEL_BEFORE_TEMPLATE:
				case static::LEVEL_ACTION_VIEW:
				case static::LEVEL_NO_RENDER:
					$this->disableLevel[$level] = true;
					break;
				default:
					throw new \InvalidArgumentException('Invalid render level ' . $level);
			}
		}

		return $this;
	}

	/**
	 * Set the views directory
	 *
	 * Sets views directory. Depending of your platform, always add a trailing slash or backslash
	 * @param string $dir
	 * @return View
	 */
	public function setViewsDir($directory)
	{
		if (is_string($directory) && strpos($directory, DIRECTORY_SEPARATOR) !== false) {
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
	 *
	 * Sets the layouts sub-directory. Must be a directory under the views directory.
	 * Depending of your platform, always add a trailing slash or backslash
	 * @param string $dir
	 * @return View
	 */
	public function setLayoutsDir($directory)
	{
		if (is_string($directory) && strpos($directory, DIRECTORY_SEPARATOR) !== false) {
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
	 *
	 * Sets a partials sub-directory. Must be a directory under the views directory.
	 * Depending of your platform, always add a trailing slash or backslash
	 * @param string $dir
	 * @return View
	 */
	public function setPartialsDir($directory)
	{
		if (is_string($directory) && strpos($directory, DIRECTORY_SEPARATOR) !== false) {
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
#	public function getMainView()
#	{
#		return $this->mainView;
#	}

	/**
	 * Sets the template before view
	 * @param string|array $templateBeforeView
	 * @return View
	 */
	public function setTemplateBefore($templateBeforeView)
	{
		if (is_array($templateBeforeView)) {
			$this->templateBeforeView = $templateBeforeView;
		} else {
			$this->templateBeforeView = array($templateBeforeView);
		}
		return $this;
	}

	/**
	 * Get the template before view
	 * @return array
	 */
#	public function getTemplateBefore()
#	{
#		return $this->templateBeforeView;
#	}

	/**
	 * Resets any template before layouts
	 * @return View
	 */
	public function cleanTemplateBefore()
	{
		$this->templateBeforeView = null;
		return $this;
	}

	/**
	 * Sets the template after view
	 * @param string $templateBeforeView
	 * @return View
	 */
	public function setTemplateAfter($templateAfterView)
	{
		if (is_array($templateAfterView)) {
			$this->templateAfterView = $templateAfterView;
		} else {
			$this->templateAfterView = array($templateAfterView);
		}
		return $this;
	}

	/**
	 * Get the template before view
	 * @return array
	 */
#	public function getTemplateAfter()
#	{
#		return $this->templateAfterView;
#	}

	/**
	 * Resets any template after layouts
	 * @return View
	 */
	public function cleanTemplateAfter()
	{
		$this->templateAfterView = array();
		return $this;
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
			$this->viewVars[$key] = $value;
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
	 * Set the controller name
	 * @param string $controllerName
	 * @return View
	 */
	protected function setControllerName($controllerName)
	{
		$this->controllerName = (string) $controllerName;
		return $this;
	}

	/**
	 * Set the action name
	 * @param string $actionName
	 * @return View
	 */
	protected function setActionName($actionName)
	{
		$this->actionName = (string) $actionName;
		return $this;
	}

	/**
	 * Starts rendering process enabling the output buffer
	 */
	public function start()
	{
		if ($this->renderStart === false) {
			$this->renderStart = true;
			ob_start();
		}
	}

	/**
	 * Finish rendering process disabling the output buffer
	 */
	public function finish()
	{
		// Assign output buffer from view to $content
		$this->renderStart === true && $this->content = ob_get_clean();

		while (ob_get_level() > 1) {
			ob_get_clean();
		}
	}

	/**
	 * Pick the view script to render
	 *
	 * Picks which view to render by passing a string with a format of controller/action
	 * or just action. Example: pick('posts/add') or pick('add')
	 * @param string
	 * @return View
	 */
	protected function pick($pickView)
	{
		if (is_array($pickView)) {
			$this->pickView = $pickView;
		} elseif (strpos($pickView, DIRECTORY_SEPARATOR) === true) {
			// Parse controller/action into array and assign
			$this->pickView = explode('/', $pickView);
		} else {
			null === $this->pickView && $this->pickView = array();
			$this->pickView[1] = $pickView;
		}

		return $this;
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
		count($params) > 0 && $this->viewVars = array_merge($this->viewVars, $params);

		// Define layoutsDir if not set
		null === $this->layoutsDir && $this->layoutsDir = 'layouts/';

		// Set controller and action name if they were not picked
		$this->controllerName = isset($this->pickView[0]) ? $this->pickView[0] : $controllerName;
		$this->actionName = isset($this->pickView[1]) ? $this->pickView[1] : $actionName;

		// @todo - init cache
		// @todo - event manager view:beforeRender

		// Get the current content in the buffer maybe some output from the controller
		$this->content = ob_get_contents();

		// If render level is > 0
		if ($this->renderLevel > 0) {
			// LEVEL_ACTION_VIEW - Action view
			if ($this->renderLevel >= static::LEVEL_ACTION_VIEW && !isset($this->disableLevel[static::LEVEL_ACTION_VIEW])) {
				$viewPath = $this->viewsDir . $this->controllerName . '/' . $this->actionName . $this->viewSuffix;
				$this->engineRender($viewPath, true, true);
			}

			// LEVEL_BEFORE_TEMPLATE - Template before view
			if ($this->renderLevel >= static::LEVEL_BEFORE_TEMPLATE && !isset($this->disableLevel[static::LEVEL_BEFORE_TEMPLATE])) {
				$silence = false;
				foreach ($this->templateBeforeView as $template) {
					$viewPath = $this->viewsDir . $this->layoutsDir . $template . $this->viewSuffix;
					$this->engineRender($viewPath, true, true);
				}
				$silence = true;
			}

			// LEVEL_LAYOUT - Controller template view
			if ($this->renderLevel >= static::LEVEL_LAYOUT && !isset($this->disableLevel[static::LEVEL_LAYOUT])) {
				$viewPath = $this->viewsDir . $this->layoutsDir . $this->controllerName . $this->viewSuffix;
				$this->engineRender($viewPath, true, true);
			}

			// LEVEL_AFTER_TEMPLATE - Template after view
			if ($this->renderLevel >= static::LEVEL_AFTER_TEMPLATE && !isset($this->disableLevel[static::LEVEL_AFTER_TEMPLATE])) {
				$silence = false;
				foreach ($this->templateAfterView as $template) {
					$viewPath = $this->viewsDir . $this->layoutsDir . $template . $this->viewSuffix;
					$this->engineRender($viewPath, true, true);
				}
				$silence = true;
			}

			// LEVEL_MAIN_LAYOUT - Main view
			if ($this->renderLevel >= static::LEVEL_MAIN_LAYOUT && !isset($this->disableLevel[static::LEVEL_MAIN_LAYOUT])) {
				$viewPath = $this->viewsDir . $this->mainView . $this->viewSuffix;
				$this->engineRender($viewPath, true, true);
			}
		}
	}

	/**
	 * Checks whether view exists on registered extensions and render it
	 * @param string $viewPath
	 * @param bool $silence
	 * @param bool $mustClean
	 * @param CacheInterface $cache
	 * @throws \Exception
	 */
	protected function engineRender($viewPath, $silence = false, $mustClean = false, $cache = null)
	{
error_log('engineRender: ' . $viewPath);

		// Clean output buffer
		$mustClean === true && ob_clean();

		if ($viewPath = stream_resolve_include_path($viewPath)) {
			// Extract data to local variables
			extract($this->viewVars, EXTR_REFS);

			// @todo - eventsManager view:beforeRenderView

			// Include the view
			include $viewPath;

			// Get contents of output buffer
			$mustClean === true && $this->content = ob_get_contents();

			// @todo - eventsManager view:afterRenderView
		} elseif ($silence === false) {
			throw new \Exception('View ' . $viewPath . ' was not found in the views directory');
		}
	}

	/**
	 * Render a partial view
	 * @param string $partialPath, the partial view file
	 * @return string
	 */
	public function partial($partialPath)
	{
		$viewPath = $this->viewsDir . $this->partialsDir . $partialPath;
		$this->engineRender($viewPath, false, false);
		return $this->content;
	}
}
