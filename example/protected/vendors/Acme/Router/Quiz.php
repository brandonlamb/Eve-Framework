<?php
/**
 * Acme
 *
 * @author Brandon Lamb
 * @copyright 2012
 * @package Acme\Router
 * @version 0.1.0
 */
namespace Acme\Router;

// Namespace aliases
use Eve\Mvc\Router as Router;

class Quiz extends Router\AbstractRouter
{
	public function route(Mvc\Request $request)
	{
		// Get request config options
		$config = \Eve::app()->config->components['request'];

		// Trim whitespace around uri, if its blank then assign an array with default module
		$uri = trim($request->uri(), '/');
		if (empty($uri)) {
			$parts = array($config['default']['module']);
		} else {
			$parts = explode('/', $uri);
		}

		// We must have at least 1 part (module)
		$numParts = count($parts);
		if ($numParts == 0) { return false; }

		// Must be exactly one part and must match regex
		if ($numParts !== 1 || !preg_match('/^q([0-9a-z]){8}$/', $parts[0])) { return false; }

		$request->module('main');
		$request->controller('quiz');
		$request->action('quiz');
		$request->routed(true);
		$request->setParam('param1', $parts[0]);

		return $request->routed();
	}
}
