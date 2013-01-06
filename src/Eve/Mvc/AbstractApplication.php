<?php
namespace Eve\Mvc;

abstract class AbstractApplication
{
	/**
	 * @var Eve\DI
	 */
	private $di;

	/**
	 * Set the DI container object
	 *
	 * @param  Eve\DiInterface      $di
	 * @return Application
	 */
	final public function setDI(\Eve\DiInterface $di)
	{
		$this->di = $di;

		return $this;
	}

	/**
	 * Get the DI container object or create a new instance if one is not set
	 *
	 * @return \Eve\DiInterface
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
	 *
	 * @return Http\Response
	 */
	public function handle()
	{
		$config     = $this->di->getShared('config');
		$dispatcher = $this->di->getShared('dispatcher');
		$router     = $this->di->getShared('router');
		$request    = $this->di->getShared('request');
		$response   = $this->di->getShared('response');

		// Append default mvc routes to router
		$this->addDefaultRoutes($router);

		// Attempt to route the request
		$router->match($request->getUri(), $request->getMethod());

		// Check if we need to load a module
		$routeTarget = $router->getRoute()->getTarget();
		if (isset($routeTarget['path']) && ($modulePath = stream_resolve_include_path($routeTarget['path'])) !== false) {
			include_once $modulePath;

			// Throw exception if the module class is not loaded
			if (class_exists($routeTarget['className']) === false) {
				throw new \Exception('Could not load module class ' . $routeTarget['className']);
			}

			// Instantiate module class
			$module = new $routeTarget['className']();
			$module->registerAutoloaders($this->di);
			$module->registerServices($this->di);
		}

		// Configure dispatcher
		$dispatcher->setControllerName($router->getControllerName());
		$dispatcher->setActionName($router->getActionName());
		$dispatcher->setParams(explode('/', trim($router->getRoute()->getParameter('params'), '/')));
		$dispatcher->dispatch();

		// Parse the view
		$view = $this->di->getShared('view');
		$view->start();
		$view->render($dispatcher->getControllerName(), $dispatcher->getActionName());
		$view->finish();

		// Set the response content from the view content
		$response->setBody($view->getContent());

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
