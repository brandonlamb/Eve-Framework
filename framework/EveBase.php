<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @copyright 2012
 * @package Eve
 * @version 0.1.0
 */
namespace Eve;

class EveBase
{
	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_CACHE		= 'cache';
	const RES_CONFIG	= 'config';
	const RES_DISPATCH	= 'dispatcher';
	const RES_FILE		= 'Cache\File';
	const RES_HTML		= 'View\HTML';
	const RES_JSON		= 'View\JSON';
	const RES_LOADER	= 'autoloader';
	const RES_REQUEST	= 'request';
	const RES_RESPONSE	= 'response';

	/**
	 * Instance of self
	 *
	 * @var Eve\Base
	 */
	protected static $_instance;

	/**
	 * Components array
	 *
	 * @var array
	 */
	protected $_components = array();

	/**
	 * Modules array
	 *
	 * @var array
	 */
	protected $_modules = array();

	/**
	 * Extensions array
	 *
	 * @var array
	 */
#	protected $_extensions = array();

	/**
	 * Base Constructor. Sets up default Mvc\Autoloader
	 *
	 * @return void
	 */
	protected function __construct($config)
	{
		// Require autoloader class
		require_once dirname(__FILE__) . '/Mvc/Config.php';

		// Config is special case and is saved as 'Config' resource for convenience
		$this->setComponent(static::RES_CONFIG, new Mvc\Config($config));
	}

	/**
	 * Enforce singleton; disallow __clone
	 *
	 * @return void
	 */
	protected final function __clone() {}

	/**
	 * Enforce singleton; disallow __sleep
	 *
	 * @return void
	 */
	protected final function __sleep() {}

	/**
	 * Enforce singleton; disallow __wake
	 *
	 * @return void
	 */
	protected final function __wake() {}

	/**
	 * Destruct
	 *
	 * @return void
	 */
	public function __destruct()
	{
		static::$_instance = null;
	}

	/**
	 * Get magic method that will try to return a component
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$config = $this->getComponent(static::RES_CONFIG)->get('components');

		// If config entry exists for component then load it
		if (isset($config[$key])) {
			return $this->getComponent($key, $config[$key]);
		} else {
			throw new Exception('Component ' . $key . ' is not registered');
		}
	}

	/**
	 * Create new instance of Eve\App. This is the entry into this class
	 *
	 * @return Eve\App
	 */
	public static function init($config)
	{
		static::$_instance = new static($config);
		return static::$_instance->_init(static::$_instance->getComponent(static::RES_CONFIG));
	}

	/**
	 * Return the static instance.
	 *
	 * @return Eve_App
	 */
	public static function app()
	{
		return self::$_instance;
	}

	/**
	 * Return entire array of components
	 *
	 * @return array
	 */
	public function getComponents()
	{
		return $this->_components;
	}

	/**
	 * Check if an application component exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isComponent($name)
	{
		return (isset($this->_components[$name]) && is_object($this->_components[$name])) ? true : false;
	}

	/**
	 * Get an application component
	 *
	 * @param string $name
	 * @param mixed $params
	 * @return object
	 */
	public function getComponent($name, $params = null)
	{
		return (isset($this->_components[$name]) || $this->loadComponent($name, $params)) ?
			$this->_components[$name] : null;
	}

	/**
	 * Set an application component
	 *
	 * @param string $name
	 * @param object $object
	 * @return void
	 */
	public function setComponent($name, $object)
	{
		$this->_components[$name] = $object;
	}

	/**
	 * Load an application component
	 *
	 * @param string $name
	 * @param mixed $params
	 * @return bool
	 */
	public function loadComponent($name, $params = null)
	{
		// Attempt to load a class from the specified name
		if (null === $params) {
			$config = $this->_components[static::RES_CONFIG]->get('components');
			$params = $config[$name];
		}
		$class = $params['class'];

		// If file option is set then attempt including it
		if (isset($params['file'])) {
			require $params['file'];
			if (false === class_exists($class)) {
				return false;
			}
		}
		$obj = (null === $params) ? new $class($this) : new $class($params);
		$this->setComponent($name, $obj);
		return true;
	}

	/**
	 * Return entire array of modules
	 *
	 * @return array
	 */
	public function getModules()
	{
		return $this->_modules;
	}

	/**
	 * Setup config and run other init methods
	 *
	 * @param string $configFile
	 * @return Base
	 */
	protected function _init($config)
	{
		// Autoloader is special so manually load it. Save autoloader as an application component
		$components = $config->get('components');
		$loader = $this->getComponent(static::RES_LOADER, $components['autoloader']);

		// Configure the SplClassLoader to act normally or silently
		$loader->setMode(Mvc\SplClassLoader::MODE_DEBUG);

		// Allow to PHP use the include_path for file path lookup
		$loader->setIncludePathLookup(true);

		// Register the autoloader, prepending it in the stack
		$loader->register(true);

		// Load any preconfigured namespaces for the autoloader
		if (isset($components['autoloader']['ns'])) {
			foreach ($components['autoloader']['ns'] as $ns => $paths) {
				$loader->add($ns, $paths);
			}
		}

		$this->_preloadComponents($config);
		$this->_initModules($config);

		return $this;
	}

	/**
	 * Preload component objects
	 *
	 * @param Mvc\Config $config
	 * @return void
	 */
	protected function _preloadComponents(Mvc\Config $config)
	{
		// Configure preloaded namespaces
		if (is_array($config->get('preload')) && $preload = $config->get('preload')) {
			// Preload components if they are not already loaded
			$components = $config->get('components');
			foreach ($preload as $component) {
				if (isset($components[$component]) && !isset($this->_components[$component])) {
					$this->loadComponent($component, $components[$component]);
				}
			}
		}
	}

	/**
	 * Load module config classes
	 *
	 * @param Mvc\Config $config
	 * @return void
	 */
	protected function _initModules(Mvc\Config $config)
	{
		// Configure module settings
		if (is_array($config->get('modules'))) {
			$modules = $config->get('modules');
			foreach ($modules as $module => $options) {
				$this->_modules[] = $module;
			}
		}
	}

	/**
	 * Run application
	 *
	 * @return void
	 */
	public function run()
	{
		// Dispatch request
		$request = $this->getComponent(static::RES_REQUEST);
		$this->getComponent(static::RES_DISPATCH)->route($request)->dispatch($request);

		// Send response
		$this->getComponent(static::RES_RESPONSE)->send();
	}

	/**
	 * Output benchmark stats
	 * @return void
	 */
	public static function shutdown()
	{
		$now = microtime(true);

		$data = json_encode(array(
			'Runtime in milliseconds' => number_format(($now - \START_TIME) * 1000, 2),
			'Runtime in microseconds' => number_format(($now - \START_TIME), 5),
			'Peak memory in KB' => number_format(memory_get_peak_usage() / 1024, 2),
			'Included files' => count(get_included_files()),
		));

		echo "\n<script>console.log($data);</script>";

		/*
		echo "\n<script>console.log('Runtime: " .number_format(($now - \START_TIME) * 1000, 2) . " ms / ",
			number_format(($now - \START_TIME), 5) . " microseconds / ",
			'Peak memory: ' . number_format(memory_get_peak_usage() / 1024, 2) . "KB / ",
#			'Peak memory: ' . memory_get_peak_usage() / 1024 . "KB<br/>\n",
			'Included files: ' . count(get_included_files()) . "');</script>";
		*/

		/*
		echo "\n<script>console.log('",
			number_format(memory_get_usage() / 1024, 2) . 'K / ',
			number_format(memory_get_peak_usage() / 1024, 2) . 'K / ',
			round((microtime(true) - \START_TIME), 5) . ' / ',
			"');</script>";
		*/
	}
}
