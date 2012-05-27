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

interface RouterInterface
{
	/**
	 * Attempt to route a request. Returns true on successful routing
	 *
	 * @param Mvc\Request $request
	 * @return bool
	 */
	public function route(Mvc\Request $request);
}
