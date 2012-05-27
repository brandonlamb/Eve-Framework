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

// Namespace aliases
use Eve\Mvc\Router as Router;

class Dispatcher extends Component
{
	/**
	 * Array of routers
	 *
	 * @var array
	 */
	protected $_routers = array();

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_MODULE		= 'module';
	const CONF_CONTROLLER	= 'controller';
	const CONF_ACTION		= 'action';
	const CONF_NA			= 'notAllowed';
	const CONF_NOTFOUND		= 'notFound';
	const CONF_EXCEPTION	= 'exception';

	/**
	 * Resources
	 *
	 * @var string
	 */
	const RES_CONFIG = 'config';

	/**
	 * Constructor, parse config and add routers
	 *
	 * @param array $config
	 */
	public function __construct($config)
	{
		parent::__construct($config);

		// Loop through each configured router and add to routers array
		foreach ($config['routers'] as $router => $options) {
			$this->addRouter(new $router($options));
		}

		// Append default simple router
		$this->addRouter(new Router\Simple());
	}

	/**
	 * Add a router to array of routers
	 *
	 * @param Router\RouterInterface, the router to add
	 * @param bool $prepend, whether to prepend the router to array
	 * @return void
	 */
	public function addRouter(Router\RouterInterface $router, $prepend = false)
	{
		if ($prepend === false) {
			$this->_routers[] = $router;
		} else {
			array_unshift($this->_routers, $router);
		}
	}

	/**
	 * Route the request by looping through all the routers until one returns true
	 *
	 * @param Request $request
	 * @return Dispatcher
	 */
	public function route(Request $request)
	{
		foreach ($this->_routers as $router) {
			if ($router->route($request) === true) { break; }
		}
		return $this;
	}

	/**
	 * Dispatch the request
	 *
	 * @param Request $request
	 * @return Dispatcher
	 */
	public function dispatch(Request $request)
	{
		// Get config
		$config		= \Eve::app()->getComponent(static::RES_CONFIG);

		// Define initial values
		$module		= $request->getModule();
		$controller	= $request->getController();
		$action		= $request->getAction();

		// Try and dispatch the request
		$dispatched	= false;
		$notFound	= false;
		$exception	= null;

		while (!$dispatched) {
			try {
				$controllerName = $this->getControllerName($module, $controller);
				$method = $this->getActionName($action);

				// Try and load the class. Catch exceptions for 404s
				try {
					$c = new $controllerName($request, $this, $exception);
				} catch (\Exception $e) {
					throw new DispatcherException($e->getMessage());
				}

				if (!$c instanceof AbstractController) {
					throw new DispatcherException('Unable to load controller class for ' . $controllerName);
				}

				// Try and load the action.
				// Wrap controller methods in separate try/catch so we can catch controller/model/etc errors
				if (method_exists($c, $method)) {
					try {
						// Call Controller::init() method first
						$c->init();

						// Call controller beforeAction() method
						$c->beforeAction();

						// Call action
						if (count($request->getParams()) == 0) {
							$c->$method();
						} else {
							call_user_func_array(array($c, $method), $request->getParams());
						}

						// Call controller afterAction() method if it exists
						$c->afterAction();
					} catch (\Exception $e) {
						throw new ControllerException($e->getMessage(), 0, $e->getCode(), $e->getFile(), $e->getLine());
					}
				} else {
					throw new DispatcherException('Unable to load action method for  ' . $action . '.');
				}

				// Dispatched ok
				$dispatched = true;
			} catch (ControllerException $e) {
				// Check if exception already is set, rethrow to avoid infinite loop
				if ($exception !== null) { throw $e; }

				// Exception throw inside controller (model, view, etc)
				$exception = $e;
				$error = $config->components['request']['error'];

				// Set controller to exception handling controller/action
				if (isset($error[self::CONF_CONTROLLER]) && isset($error[self::CONF_EXCEPTION])) {
					$controller = $error[self::CONF_CONTROLLER];
					$action = $error[self::CONF_EXCEPTION];
				}
			} catch (DispatcherException $e) {
				// Page not found, display error page
				if ($notFound === true) {
					throw new DispatcherException('Page not found. Unable to load error page, configuration is invalid. ' . $e->getMessage());
				} else if ($exception !== null) {
					throw new Exception('Fatal error');
				}

				$notFound = true;
				$error = $config->components['request']['error'];

				// Set controller to 404 controller/action
				if (isset($error[self::CONF_CONTROLLER]) && isset($error[self::CONF_NOTFOUND])) {
					$controller = $error[self::CONF_CONTROLLER];
					$action = $error[self::CONF_NOTFOUND];
				}
			} catch (\Exception $e) {
				// Catch any other exceptions within the application
				if ($exception) {
					throw new DispatcherException('Uncaught exception. Unable to load error page, configuration is invalid.');
				}

				$exception = $e;
				$error = $config->components['request']['error'];

				if (isset($error[self::CONF_CONTROLLER]) && isset($error[self::CONF_EXCEPTION])) {
					$controller = $error[self::CONF_CONTROLLER];
					$action = $error[self::CONF_EXCEPTION];
				}
			}
		}
	}

	/**
	 * Format controller as valid class/namespace
	 *
	 * @param string $module
	 * @param string $controller
	 * @return string
	 */
	public function getControllerName($module, $controllerName)
	{
		$controller = null;
		$parts = explode('-', $controllerName);
		foreach ($parts as $part) {
			$controller .= ucfirst($part);
		}

		return '\\' . ucfirst($module) . '\\Controller\\' . ucfirst($controller);
	}

	/**
	 * Format action
	 *
	 * @param string $action
	 * @return string
	 */
	public function getActionName($action)
	{
		// Covert dashes to capitals
		$parts = explode('-', $action);
		$action = 'action';
		foreach ($parts as $part) {
			$action .= ucfirst($part);
		}
		return $action;
	}
}
