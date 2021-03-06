<?php

use Eve\Mvc;

/**
 * @small
 */
class ViewTest extends PHPUnit_Framework_TestCase
{
	private $request;
	private $view;

	/**
	 * setUp
	 */
	public function setUp()
	{
		$_SERVER['PATH_INFO'] = '/modulex/controllerx/actionx';

		$this->request = new \Eve\Http\Request(array());
		$this->request->setModule('modulex')
			->setController('controllerx')
			->setAction('actionx');

		$this->view = new Mvc\View('/test', $this->request);
	}

	/**
	 * @covers Eve\Mvc\View::__construct
	 */
	public function testConstruct()
	{
		$view = new Mvc\View('/testPath', $this->request);
		$this->assertEquals('/testPath/Modulex', $view->getPath());
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
	 * @covers Eve\Mvc\View::setPath
	 * @covers Eve\Mvc\View::getPath
	 */
	public function testGetSetPath()
	{
		$this->assertEquals('/test/Modulex', $this->view->getPath());

		$this->view->setPath('/abc');
		$this->assertEquals('/abc/Modulex', $this->view->getPath());

#		$this->view->setPath(array(1, 2, 3));
#		$this->assertEquals('abc', $this->view->getPath());
	}

	/**
	 * @covers Eve\Mvc\View::setView
	 * @covers Eve\Mvc\View::getView
	 */
	public function testGetSetView()
	{
		$this->assertEquals('/test/Modulex/views/controllerx/actionx.php', $this->view->getView());

		$this->view->setView('abc');
		$this->assertEquals('/test/Modulex/views/controllerx/abc.php', $this->view->getView());

#		$this->view->setView(array(1, 2, 3));
#		$this->assertEquals('abc', $this->view->getView());
	}
}
