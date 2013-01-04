<?php
/**
 * Eve Application Framework
 *
 * @package Eve
 */
namespace Eve\Mvc;
use Eve\DI\InjectionAwareInterface;
use Eve\Events\EventsAwareInterface;

class Dispatcher implements InjectionAwareInterface, EventsAwareInterface
{
	use \Eve\DI\InjectableTrait;

	const MAX_DISPATCH_LOOPS = 5;

	/**
	 * @var string, Controller suffix
	 */
	protected $controllerSuffix = 'Controller';

	/**
	 * @var string, Action suffix
	 */
	protected $actionSuffix = 'Action';

	/**
	 * @var string, default namespace name
	 */
	protected $defaultNamespace;

	/**
	 * @var string, namespace name
	 */
	protected $namespaceName;

	/**
	 * @var string, default controller name
	 */
	protected $defaultControllerName = 'Index';

	/**
	 * @var string, default action name
	 */
	protected $defaultActionName = 'Index';

	/**
	 * @var string, Controller name
	 */
	protected $controllerName;

	/**
	 * @var string, Action name
	 */
	protected $actionName;

	/**
	 * @var Controller, the last controller instantiated
	 */
	protected $lastController;

	/**
	 * @var Controller, the active controller instance
	 */
	protected $activeController;

	/**
	 * @var array, action parameters
	 */
	protected $params;

	/**
	 * @var int, number of loops dispatched
	 */
	protected $dispatchLoops = 0;

	/**
	 * Set the controller suffix
	 *
	 * @param string $suffix
	 * @return Dispatcher
	 */
	public function setControllerSuffix($suffix)
	{
		if (!empty($suffix)) {
			$this->controllerSuffix = (string) $suffix;
		}
		return $this;
	}

	/**
	 * Get the controller suffix
	 *
	 * @return string
	 */
	public function getControllerSuffix()
	{
		return $this->controllerSuffix;
	}

	/**
	 * Set the action suffix
	 *
	 * @param string $suffix
	 * @return Dispatcher
	 */
	public function setActionSuffix($suffix)
	{
		if (!empty($suffix)) {
			$this->actionSuffix = (string) $suffix;
		}
		return $this;
	}

	/**
	 * Get the action suffix
	 *
	 * @return string
	 */
	public function getActionSuffix()
	{
		return $this->actionSuffix;
	}

	/**
	 * Set the namespace name
	 *
	 * @param string $namespaceName
	 * @return Dispatcher
	 */
	public function setNamespaceName($namespaceName)
	{
		if (!empty($namespaceName)) {
			$this->namespaceName = (string) $namespaceName;
		}
		return $this;
	}

	/**
	 * Get the namespace name
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName;
	}

	/**
	 * Set the controller name
	 *
	 * @param string $controllerName
	 * @return Dispatcher
	 */
	public function setControllerName($controllerName)
	{
		if (!empty($controllerName)) {
			$this->controllerName = (string) $this->camelize($controllerName) . $this->controllerSuffix;
		}
		return $this;
	}

	/**
	 * Get the controller name
	 *
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * Set the action name
	 *
	 * @param string $actionName
	 * @return Dispatcher
	 */
	public function setActionName($actionName)
	{
		if (!empty($actionName)) {
			$this->actionName = (string) $this->camelize($actionName) . $this->actionSuffix;
		}
		return $this;
	}

	/**
	 * Get the action name
	 *
	 * @return string
	 */
	public function getActionName()
	{
		return $this->actionName;
	}

	/**
	 * Set the default namespace name
	 *
	 * @param string $namespaceName
	 * @return Dispatcher
	 */
	public function setDefaultNamespace($namespaceName)
	{
		if (!empty($namespaceName)) {
			$this->defaultNamespace = (string) $namespaceName;
		}
		return $this;
	}

	/**
	 * Get default namespace name
	 *
	 * @return string
	 */
	public function getDefaultNamespace()
	{
		return $this->defaultNamespace;
	}

	/**
	 * Set the default controller name
	 *
	 * @param string $controllerName
	 * @return Dispatcher
	 */
	public function setDefaultController($controllerName)
	{
		if (!empty($controllerName)) {
			$this->defaultControllerName = (string) $this->camelize($controllerName) . $this->controllerSuffix;
		}
		return $this;
	}

	/**
	 * Set the default action name
	 *
	 * @param string $actionName
	 * @return Dispatcher
	 */
	public function setDefaultAction($actionName)
	{
		if (!empty($actionName)) {
			$this->defaultActionName = (string) $this->camelize($actionName) . $this->actionSuffix;
		}
		return $this;
	}

	/**
	 * Set the last controller
	 *
	 * @param ControllerInterface $controller
	 * @return Dispatcher
	 */
	protected function setLastController(ControllerInterface $controller)
	{
		$this->lastController = $controller;
		return $this;
	}

	/**
	 * Get the last controller instantiated
	 *
	 * @return ControllerInterface
	 */
	public function getLastController()
	{
		if (null === $this->lastController) {
			return $this->getActiveController();
		}
		return $this->lastController;
	}

	/**
	 * Set the current controller
	 *
	 * @param ControllerInterface $controller
	 * @return Dispatcher
	 */
	public function setActiveController(ControllerInterface $controller)
	{
		$this->activeController = $controller;
		return $this;
	}

	/**
	 * Get the active controller. If no controller is yet instantiated, an except is thrown
	 *
	 * @throws Dispatcher\Exception
	 * @return ControllerInterface
	 */
	public function getActiveController()
	{
		if (null === $this->activeController || !$this->activeController instanceof ControllerInterface) {
			throw new Dispatcher\Exception('No active controller has been instantiated');
		}
		return $this->activeController;
	}

	/**
	 * Set action parameters
	 *
	 * @param array $params
	 * @return Dispatcher
	 */
	public function setParams(array $params = array())
	{
		$this->params = $params;
		return $this;
	}

	/**
	 * Get action parameters
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Set a param by its name or numeric index
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return Dispatcher
	 */
	public function setParam($key, $value)
	{
		if (is_string($key) || is_numeric($key)) {
			$this->params[$key] = $value;
		}
		return $this;
	}

	/**
	 * Get a param by its name or numeric index
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	/**
	 * Forward the execution flow to another controller/action
	 *
	 * @param array $forward
	 */
	public function forward($forward)
	{
		// Set the new namespace/module if passed
		isset($forward['module']) && $this->setNamespace($forward['module']);
		isset($forward['namespace']) && $this->setNamespace($forward['namespace']);

		// Set the new controller if passed
		isset($forward['controller']) && $this->setControllerName($forward['controller']);

		// Set the new action if passed
		isset($forward['action']) && $this->setActionName($forward['action']);

		// Dispatch the new request
		$this->dispatch();
	}

	/**
	 * Get camelized string, replacing all non word characters
	 *
	 * @param string $word
	 * @return string
	 */
	protected function camelize($word)
	{
		static $cached;
		if (!isset($cached[$word])) {
			$cached[$word] = (string) str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9^:]+/', ' ', $word)));
		}
		return $cached[$word];
	}

	/**
	 * Dispatch the request
	 *
	 * @throws Dispatcher\Exception
	 * @return Dispatcher
	 */
	public function dispatch()
	{
		$di	= $this->getDI();
		$config = $di->getShared('config')->get('router');
		$autoloader = $di->getShared('loader');
		$routeTarget = $di->getShared('router')->getRoute()->getTarget();

		// If activeController is already an instantiated instance, save it to lastController
		if ($this->activeController instanceof Controller) {
			$this->lastController = $this->activeController;
		}

		// Throw exception if we have exceeded max dispatch loops
		if ($this->dispatchLoops > static::MAX_DISPATCH_LOOPS) {
			throw new Dispatcher\Exception('Max dispatch loops exceeded');
		}

		// Increment dispatch loop count
		$this->dispatchLoops++;

		try {
			// Check if we need to load a module
			if (isset($routeTarget['path']) && ($modulePath = stream_resolve_include_path($$routeTarget['path'])) !== false) {
				include_once $modulePath;

				// Throw exception if the module class is not loaded
				if (class_exists($routeTarget['className']) === false) {
					throw new Dispatcher\Exception('Could not load module class ' . $routeTarget['className']);
				}

				// Instantiate module class
				$module = new $routeTarget['className']();
				$module->registerServices($di);
			}

			// If namespace is defined, prepend it to classname from controllerName
			if (null !== $this->namespaceName) {
				$className = $this->namespaceName . '\\' . $this->controllerName;
			} elseif (null !== $this->defaultNamespace) {
				// namespaceName wasnt defined but defaultNamespace was so use it
				$className = $this->defaultNamespace . '\\' . $this->controllerName;
			} else {
				// No namespaceName or defaultNamespace set so just use the controllerName
				$className = $this->controllerName;
			}

			// Throw exception if the controller class is not loaded
			if (class_exists($className) === false) {
				throw new Dispatcher\Exception('Could not load controller class ' . $className);
			}

			// Attempt to instantiate the contrller class
			$this->activeController = new $className();

			// If the class object is not an instance of the Controller class then throw exception
			if (!$this->activeController instanceof ControllerInterface) {
				throw new Dispatcher\Exception('Unable to load controller class for ' . $className);
			}

			// Verify the requested action is callable
			if (!method_exists($this->activeController, $this->actionName)) {
				throw new Dispatcher\Exception('Unable to load action method for  ' . $this->actionName . '.');
			}

			// Call Controller::init() method first
			$this->activeController->init();

			// Call controller beforeDispatch() method
			$this->activeController->beforeDispatch();

			// Call action
			if (count($this->params) === 0) {
				$this->activeController->{$this->actionName}();
			} else {
				call_user_func_array(array($this->activeController, $this->actionName), $this->params);
			}

			// Call controller afterDispatch() method if it exists
			$this->activeController->afterDispatch();
		} catch (Dispatcher\Exception $e) {
			// Caught an error, if no defined error controller then throw exception
			if (!isset($config['error']['controller'], $config['error']['action'])) {
				throw $e;
			}

			// Error controller defined, forward to the set error handler
			$this->setParams(array($e->getMessage()));
			$this->forward(array(
				'controller'	=> $config['error']['controller'],
				'action'		=> $config['error']['action'],
			));
		} catch (\Exception $e) {
			throw $e;
		}
	}
}
