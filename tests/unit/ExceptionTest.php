<?php

/**
 * @small
 */
class ExceptionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers Eve\Exception::__construct
	 */
	public function testCreate()
	{
		$exception = new \Eve\Exception();
		$this->assertInstanceOf('\Eve\Exception', $exception);
	}
}
