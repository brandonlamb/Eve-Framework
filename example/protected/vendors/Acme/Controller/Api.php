<?php
/**
 * @package Acme
 */
namespace Acme\Controller;

// Namespace aliases
use Eve\Mvc as Mvc;

class Controller extends Mvc\Controller
{
	/**
	 * Setup global options for extending controllers
	 */
	public function init()
	{
		\Eve::app()->response->header('Content-Type', 'application/json');
	}

	/**
	 * Set response body to json_encode(data)
	 *
	 * @param array $data
	 */
	public function render($data = array())
	{
		\Eve::app()->getComponent(self::RES_RESPONSE)->body(json_encode($data));
	}
}
