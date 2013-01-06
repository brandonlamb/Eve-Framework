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
	 * @var string
	 */
	protected $templateBeforeView;

	/**
	 * Template after view
	 *
	 * Rendered after the action view
	 * @var string
	 */
	protected $templateAfterView;

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
			default:
				throw new \InvalidArgumentException('Invalid render level');
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
	public function getMainView()
	{
		return $this->mainView;
	}

	/**
	 * Sets the template before view
	 * @param string $templateBeforeView
	 * @return View
	 */
	public function setTemplateBefore($templateBeforeView)
	{
		$this->templateBeforeView = (string) $templateBeforeView;
		return $this;
	}

	/**
	 * Get the template before view
	 * @return string
	 */
	public function getTemplateBefore()
	{
		return $this->templateBeforeView;
	}

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
		$this->templateAfterView = (string) $templateAfterView;
		return $this;
	}

	/**
	 * Get the template before view
	 * @return string
	 */
	public function getTemplateAfter()
	{
		return $this->templateAfterView;
	}

	/**
	 * Resets any template after layouts
	 * @return View
	 */
	public function cleanTemplateAfter()
	{
		$this->templateAfterView = null;
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

		$this->controllerName = $controllerName;
		$this->actionName = $actionName;
echo "$controllerName / $actionName\n";

		// Catch any exceptions/errors that happen inside a view
		try {
			// Extract data to local variables
			extract($this->viewVars, EXTR_REFS);

			// LEVEL_MAIN_LAYOUT - Main view
			if ($this->renderLevel <= static::LEVEL_MAIN_LAYOUT && !isset($this->disableLevel[static::LEVEL_MAIN_LAYOUT])) {
				$viewPath = $this->viewsDir . $this->mainView . $this->viewSuffix;
				if ($viewPath = stream_resolve_include_path($viewPath)) {
					include $viewPath;
				}
			}

			// LEVEL_AFTER_TEMPLATE - Template after view
			if ($this->renderLevel <= static::LEVEL_AFTER_TEMPLATE && !isset($this->disableLevel[static::LEVEL_AFTER_TEMPLATE])) {
				$viewPath = $this->viewsDir . $this->layoutsDir . $this->templateAfterView . $this->viewSuffix;
				if ($viewPath = stream_resolve_include_path($viewPath)) {
					include $viewPath;
				}
			}

			// LEVEL_LAYOUT - Controller template view
			if ($this->renderLevel <= static::LEVEL_LAYOUT && !isset($this->disableLevel[static::LEVEL_LAYOUT])) {
				$viewPath = $this->viewsDir . $this->layoutsDir . $this->controllerName . $this->viewSuffix;
				if ($viewPath = stream_resolve_include_path($viewPath)) {
					include $viewPath;
				}
			}

			// LEVEL_BEFORE_TEMPLATE - Template before view
			if ($this->renderLevel <= static::LEVEL_BEFORE_TEMPLATE && !isset($this->disableLevel[static::LEVEL_BEFORE_TEMPLATE])) {
				$viewPath = $this->viewsDir . $this->layoutsDir . $this->templateBeforeView . $this->viewSuffix;
				if ($viewPath = stream_resolve_include_path($viewPath)) {
					include $viewPath;
				}
			}

			// LEVEL_ACTION_VIEW - Action view
			if ($this->renderLevel <= static::LEVEL_ACTION_VIEW && !isset($this->disableLevel[static::LEVEL_ACTION_VIEW])) {
				$viewPath = $this->viewsDir . $this->layoutsDir . $this->actionName . $this->viewSuffix;
				if ($viewPath = stream_resolve_include_path($viewPath)) {
					include $viewPath;
				}
			}
		} catch (\Exception $e) {
			$this->finish();
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
		// Catch any exceptions/errors that happen inside a view
		try {
			$viewPath = $this->viewsDir . $this->partialsDir . $partialPath . $this->viewSuffix;
			if ($viewPath = stream_resolve_include_path($viewPath)) {
				return include $viewPath;
#				return $content;
			}
		} catch (\Exception $e) {
			while (ob_get_level() > 1) { ob_get_clean(); }
			throw $e;
		}
	}
}
