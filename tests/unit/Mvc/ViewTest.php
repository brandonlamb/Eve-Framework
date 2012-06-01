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

	/**
	 * @covers Eve\Mvc\View::__set
	 * @covers Eve\Mvc\View::__get
	 */
	public function testMagicSetGet()
	{
		$this->view->testProperty = 'testValue';
		$this->assertEquals('testValue', $this->view->testProperty);
		$this->assertNull($this->view->nonExistentProperty);
	}

	/**
	 * @covers Eve\Mvc\View::set
	 * @covers Eve\Mvc\View::get
	 */
	public function testSetGet()
	{
		$this->assertNull($this->view->get('nonExistentProperty'));

		$this->view->set('testProperty', 'testValue');
		$this->assertEquals('testValue', $this->view->get('testProperty'));

		$this->view->set('testProperty', 'newTestValue');
		$this->assertEquals('newTestValue', $this->view->get('testProperty'));
	}
}
