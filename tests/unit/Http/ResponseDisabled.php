<?php

use Eve\Http;

/**
 * @small
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{
	private $response;

	/**
	 * setUp
	 */
	public function setUp()
	{
		$this->response = new Http\Response();
	}

	/**
	 * @covers Eve\Http\Response::getHeaders
	 */
	public function testNoHeadersAreInitiallySet()
	{
		$this->assertEmpty($this->response->getHeaders());
	}

	/**
	 * @covers Eve\Http\Response::getHeaders
	 */
	public function testGetHeaders()
	{
		$this->response->setHeader('Test Header', 'Test Value');
		$this->assertArrayHasKey('Test Header', $this->response->getHeaders());
	}

	/**
	 * @covers Eve\Http\Response::setHeader
	 */
	public function testSetHeader()
	{
		$this->response->setHeader('Content-Type', 'application/json');
		$this->assertContains('application/json', $this->response->getHeader('Content-Type'));
	}

	/**
	 * @covers Eve\Http\Response::getHeader
	 */
	public function testGetHeader()
	{
		$this->response->setHeader('Content-Type', 'application/json');
		$this->assertContains('application/json', $this->response->getHeader('Content-Type'));
	}

	/**
	 * @covers Eve\Http\Response::getBody
	 * @covers Eve\Http\Response::setBody
	 */
	public function testGetAndSetBody()
	{
		$this->assertEmpty($this->response->getBody());

		$response = $this->response->setBody('abc');
		$this->assertInstanceOf('\\Eve\Mvc\\Response', $response);
		$this->assertEquals('abc', $this->response->getBody());

		$this->response->setBody(-10);
		$this->assertEquals(-10, $this->response->getBody());
	}

	/**
	 * @covers Eve\Http\Response::setBody
	 * @covers Eve\Http\Response::getBody
	 * @covers Eve\Http\Response::clear
	 */
	public function testClearBody()
	{
		$this->assertEmpty($this->response->getBody());

		$this->response->setBody('abc');
		$this->assertEquals('abc', $this->response->getBody());
		$response = $this->response->clear();
		$this->assertInstanceOf('\\Eve\Mvc\\Response', $response);
		$this->assertEmpty($this->response->getBody());
	}

	/**
	 * @covers Eve\Http\Response::setStatus
	 */
	public function testSetStatus()
	{
		$response = $this->response->setStatus(404);
		$this->assertInstanceOf('\\Eve\Mvc\\Response', $response);
		$this->assertEquals(404, $this->response->getStatus());
	}

	/**
	 * @covers Eve\Http\Response::getStatus
	 */
	public function testGetStatus()
	{
		$this->assertEquals(200, $this->response->getStatus());

		$response = $this->response->setStatus(404);
		$this->assertEquals(404, $this->response->getStatus());

		$this->response->setStatus('invalid');
		$this->assertNotEquals('invalid', $this->response->getStatus());
		$this->assertEquals(404, $this->response->getStatus());
	}

	/**
	 * @covers Eve\Http\Response::isRedirect
	 */
	public function testIsRedirect()
	{
		$this->response->setStatus(404);
		$this->assertFalse($this->response->isRedirect());

		$this->response->setStatus(301);
		$this->assertTrue($this->response->isRedirect());

		$this->response->setStatus(302);
		$this->assertTrue($this->response->isRedirect());
	}

	/**
	 * @covers Eve\Http\Response::__toString
	 */
	public function testToString()
	{
		$this->response->setBody('abc');
		$this->assertEquals('abc', (string) $this->response);
	}
}
