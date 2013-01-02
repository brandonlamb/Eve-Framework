<?php
/**
 * Eve Application Framework
 *
 * @package Eve
 */
namespace Eve\Mvc;

class Dispatcher extends \Eve\DI\Injectable
{
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
     * Set the controller suffix
     *
     * @param string $suffix
     * @return Dispatcher
     */
    public function setControllerSuffix($suffix)
    {
        $this->controllerSuffix = (string) $suffix;
        return $this;
    }

    /**
     * Set the action suffix
     *
     * @param string $suffix
     * @return Dispatcher
     */
    public function setActionSuffix($suffix)
    {
        $this->actionSuffix = (string) $suffix;
        return $this;
    }

    /**
     * Set the namespace name
     *
     * @param string $namespaceName
     * @return Dispatcher
     */
    public function setNamespace($namespaceName)
    {
        $this->namespaceName = (string) $namespaceName;
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
        $this->controllerName = (string) $this->camelize($controllerName) . $this->controllerSuffix;
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
        $this->actionName = (string) $this->camelize($actionName) . $this->actionSuffix;
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
        $this->defaultNamespace = (string) $namespaceName;
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
        $this->defaultControllerName = (string) $this->camelize($controllerName) . $this->controllerSuffix;
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
        $this->defaultActionName = (string) $this->camelize($actionName) . $this->actionSuffix;
        return $this;
    }

    /**
     * Set the last controller
     *
     * @param Controller $controller
     * @return Dispatcher
     */
    protected function setLastController(Controller $controller)
    {
        $this->lastController = $controller;
        return $this;
    }

    /**
     * Get the last controller instantiated
     *
     * @return Controller
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
     * @param Controller $controller
     * @return Dispatcher
     */
    public function setActiveController(Controller $controller)
    {
        $this->activeController = $controller;
        return $this;
    }

    /**
     * Get the active controller. If no controller is yet instantiated, an except is thrown
     *
     * @throws Exception
     * @return Controller
     */
    public function getActiveController()
    {
        if (null === $this->activeController || !$this->activeController instanceof Controller) {
            throw new \RuntimeException('No active controller has been instantiated');
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
     * @return Dispatcher
     */
    public function forward($forward)
    {

    }


    /**
     * Dispatch the request
     *
     * @param  Request    $request
     * @return Dispatcher
     */
    public function dispatch()
    {
        $di         = $this->getDI();
        $config     = $di->getShared('config')->get('router');
        $request    = $di->getShared('request');
        $router     = $di->getShared('router');
        $params     = explode('/', trim($router->getRoute()->getParameter('params'), '/'));
        $dispatched	= false;
        $notFound	= false;
        $exception	= null;

        while (!$dispatched) {
            try {
                // If no module is set then just assigned the controller name as className, otherwise prepend the module name
                $className = $router->getModuleName() === '' ? $this->controllerName : $router->getModuleName() . '\\' . $this->controllerName;

                // Try and load the class. Catch exceptions for 404s
                try {
                    $controller = new $className();
                } catch (\Exception $e) {
                    throw new DispatcherException($e->getMessage());
                }

                if (!$controller instanceof Controller) {
                    throw new DispatcherException('Unable to load controller class for ' . $className);
                }

                // Try and load the action.
                // Wrap controller methods in separate try/catch so we can catch controller/model/etc errors
                if (method_exists($controller, $this->actionName)) {
                    try {
                        // Call Controller::init() method first
                        $controller->init();

                        // Call controller beforeDispatch() method
                        $controller->beforeDispatch();

                        // Call action
                        if (count($params) == 0) {
                            $controller->{$this->actionName}();
                        } else {
                            call_user_func_array(array($controller, $this->actionName), $params);
                        }

                        // Call controller afterDispatch() method if it exists
                        $controller->afterDispatch();
                    } catch (\ErrorException $e) {
                        throw new \RuntimeException($e->getMessage(), 0, $e->getCode(), $e->getFile(), $e->getLine());
                    } catch (\Exception $e) {
                        throw new \RuntimeException($e->getMessage(), 0, 0, $e->getFile(), $e->getLine());
                    }
                } else {
                    throw new DispatcherException('Unable to load action method for  ' . $this->actionName . '.');
                }

                // Dispatched ok
                $dispatched = true;
            } catch (\RuntimeException $e) {
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
                } elseif ($exception !== null) {
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
     * Get camelized string, replacing all non word characters
     *
     * @param string $word
     * @return string
     */
    protected function camelize($word)
    {
        static $cached;
        if (!isset($cached[$word])) {
            $cached[$word] = str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9^:]+/', ' ', $word)));
        }
        return $cached[$word];
    }
}
