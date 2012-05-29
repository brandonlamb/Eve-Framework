<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @copyright 2012
 * @package Eve\Mvc\Router
 * @version 0.1.0
 */
namespace Eve\Mvc\Router;

// Namespace aliases
use Eve\Mvc as Mvc;

class Simple extends AbstractRouter
{
	/**
	 * Resources
	 *
	 * @var string
	 */
	const RES_CONFIG = 'config';

	public function route(Mvc\Request $request)
	{
		// Get request config options
		$config = \Eve::app()->getComponent(static::RES_CONFIG)->components['request'];

		// Trim whitespace around uri, if its blank then assign an array with default module
		$uri = trim($request->getUri(), '/');
		if (empty($uri)) {
			$parts = array($config['default']['module']);
		} else {
			$parts = explode('/', $uri);
		}

		// We must have at least 1 part (module)
		$numParts = count($parts);
		if ($numParts == 0) { return false; }

		// Attempt to figure out the module
		foreach (\Eve::app()->getModules() as $module) {
			if ($module == $parts[0]) {
				$request->setModule(array_shift($parts));
			}
		}

		// Set module to default module if it wasnt set above
		if ($request->getModule() === null) {
			$request->setModule($config['default']['module']);
		}

		// Set the controller using next part, otherwise use default
		if (isset($parts[0])) {
			$request->setController(array_shift($parts));
		} else {
			$request->setController($config['default']['controller']);
		}

		// Set the action using next part, otherwise use default
		if (isset($parts[0])) {
			$request->setAction(array_shift($parts));
		} else {
			$request->setAction($config['default']['action']);
		}

		// Set optional parameters
		for ($i = 0, $c = count($parts); $i < $c; $i++) {
			$request->setParam('param' . ($i + 1), \Eve\Filter::xss($parts[$i]));
		}

		// Set routed flag
		$request->setRouted(true)->isRouted();
	}
}
