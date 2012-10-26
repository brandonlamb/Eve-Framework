<?php
namespace Eve\Mvc;

abstract class Application
{
    /**
     * @var Eve\DI
     */
    private $di;

    /**
     * Set the DI container object
     *
     * @param  Eve\DI      $di
     * @return Application
     */
    final public function setDI(\Eve\DI $di)
    {
        $this->di = $di;

        return $this;
    }

    /**
     * Get the DI container object or create a new instance if one is not set
     *
     * @return \Eve\DI
     */
    final public function getDI()
    {
        if (null === $this->di) {
            $this->di = new \Eve\DI\FactoryDefault();
        }

        return $this->di;
    }

    /**
     * Handle the request and dispatch the controller, return the response
     */
    public function handle()
    {
        $config     = $this->di->getShared('config');
        $dispatcher = $this->di->getShared('dispatcher');
        $router     = $this->di->getShared('router');
        $request    = $this->di->getShared('request');
        $response   = $this->di->getShared('response');
        $view       = $this->di->getShared('view');

        // Append default mvc routes to router
        $this->addDefaultRoutes($router);

        // Attempt to route the request
        $router->match($request->getUri(), $request->getMethod());

        // Configure dispatcher
#        $dispatcher->setModuleName($router->getModuleName());
        $dispatcher->setControllerName($router->getControllerName());
        $dispatcher->setActionName($router->getActionName());
        $dispatcher->dispatch();

#d($dispatcher);

        return $response;
    }

    /**
     * Append default mvc routes to the router
     *
     * @param Eve\Mvc\Router $router
     * @return voic
     */
    public function addDefaultRoutes(\Eve\Mvc\Router $router)
    {
        // route: /indexController/indexAction/blah1/blah2
        $router->map('/:controller/:action/:params', array('module' => ''), array(
            'filters' => array(
                'params' => '?(.*)?',
            )
        ));

        // route: /indexController
        $router->map('/:controller', array('module' => '', 'action' => 'index'), array(
            'filters' => array(
                'controller' => '?([\w-]+)/?',
            )
        ));
    }
}
