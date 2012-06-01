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
	 * @covers Eve\Mvc\View::__isset
	 * @covers Eve\Mvc\View::__unset
	 */
	public function testMagicMethods()
	{
		$this->view->testProperty = 'testValue';
		$this->assertEquals('testValue', $this->view->testProperty);
		$this->assertNull($this->view->nonExistentProperty);

		$this->assertFalse(isset($this->view->nonExistentProperty));
		$this->assertTrue(isset($this->view->testProperty));

		$this->view->test1 = 'My Value';
		$this->assertEquals('My Value', $this->view->test1);
		unset($this->view->test1);
		$this->assertFalse(isset($this->view->test1));
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

	/**
	 * @covers Eve\Mvc\View::setView
	 * @covers Eve\Mvc\View::getView
	 */
	public function testGetSetView()
	{
		$this->assertEquals('index', $this->view->getView());

		$this->view->setView('abc');
		$this->assertEquals('abc', $this->view->getView());

		$this->view->setView(array(1, 2, 3));
		$this->assertEquals('abc', $this->view->getView());
	}
}
