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

class Dispatcher extends \Eve\DI\Injectable
{
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
     * @var string, Controller suffix
     */
    protected $controllerSuffix = 'Controller';

    /**
     * @var string, Action suffix
     */
    protected $actionSuffix = 'Action';

    /**
     * @var string, Controller name
     */
    protected $controllerName;

    /**
     * @var string, Action name
     */
    protected $actionName;

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
     * Set the controller name
     *
     * @param string $controllerName
     * @return Dispatcher
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $this->camelize($controllerName) . $this->controllerSuffix;
        return $this;
    }

    /**
     * Set the action name
     *
     * @param string $actionName
     * @return Dispatcher
     */
    public function setActionName($actionName)
    {
        $this->actionName = $this->camelize($actionName) . $this->actionSuffix;
        return $this;
    }

    /**
     * Dispatch the request
     *
     * @param  Request    $request
     * @return Dispatcher
     */
    public function dispatch()
    {
        $di = $this->getDI();

        // Get config
        $config = $di->getShared('config')->get('router');
        $request = $di->getShared('request');
        $router = $di->getShared('router');
        $params = explode('/', trim($router->getRoute()->getParameter('params'), '/'));

        // Try and dispatch the request
        $dispatched	= false;
        $notFound	= false;
        $exception	= null;

        while (!$dispatched) {
            try {
                // If no module is set then just assigned the controller name as className, otherwise prepend the module name
                $className = $router->getModuleName() === '' ? $this->controllerName : $router->getModuleName() . '\\' . $this->controllerName;
                $method = $this->actionName;

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
            /*
            if (preg_match_all('/\/(.?)/', $word, $got)) {
                foreach ($got[1] as $k => $v) {
                   $got[1][$k] = '::' . strtoupper($v);
                }
                $word = str_replace($got[0], $got[1], $word);
            }
            */
            $cached[$word] = str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9^:]+/', ' ', $word)));
        }
        return $cached[$word];
    }
}
