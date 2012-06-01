<?php

use Eve\Mvc;

/**
 * @small
 */
class ViewTest extends PHPUnit_Framework_TestCase
{
	private $view;

	/**
	 * setUp
	 */
	public function setUp()
	{
		$this->view = new Mvc\View();
	}

	/**
	 * @covers Eve\Mvc\View::__construct
	 */
	public function testConstruct()
	{
		$view = new Mvc\View('testPath', 'testLayout', 'testView');
		$this->assertEquals('testPath', $view->getPath());
		$this->assertEquals('testLayout', $view->getLayout());
		$this->assertEquals('testView', $view->getView());
	}
}
