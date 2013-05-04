<?php
namespace Eve\Mvc;

use Eve\Di\DiInterface,
	Eve\Di\FactoryDefault;

abstract class AbstractApplication
{
	/**
	 * @var Eve\Di
	 */
	private $di;

	/**
	 * Set the DI container object
	 *
	 * @param  Eve\DiInterface      $di
	 * @return Application
	 */
	final public function setDI(DiInterface $di)
	{
		$this->di = $di;

		return $this;
	}

	/**
	 * Get the DI container object or create a new instance if one is not set
	 *
	 * @return DiInterface
	 */
	final public function getDI()
	{
		if (null === $this->di) {
			$this->di = new FactoryDefault();
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

		$view = $this->di->getShared('view');
		$view->start();

		$dispatcher->dispatch();

		// Parse the view
		try {
			$view->render($router->getControllerName(), $router->getActionName());
			$view->finish();
		} catch (\Exception $e) {
			$view->finish();
			$view->start();
			$dispatcher->setParams([$e->getMessage()]);
			$dispatcher->forward(['controller' => 'error', 'action' => 'index']);
			$view->render($router->getControllerName(), $router->getActionName());
			$view->finish();
		}

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
		$router->map('/:controller/:action/:params', ['module' => ''], [
			'filters' => [
				'params' => '?(.*)?',
			]
		]);

		// route: /indexController
		$router->map('/:controller', ['module' => '', 'action' => 'index'], [
			'filters' => [
				'controller' => '?([\w-]+)/?',
			]
		]);
	}
}
