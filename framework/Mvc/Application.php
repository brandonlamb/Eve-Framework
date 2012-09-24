<?php
namespace Eve\Mvc;

abstract class Application
{
	/**
	 * @var Eve\DI
	 */
	private $di;

	/**
	 * Set the DI container object
	 *
	 * @param Eve\DI $di
	 * @return Application
	 */
	public final function setDI(\Eve\DI $di)
	{
		$this->di = $di;
		return $this;
	}

	/**
	 * Get the DI container object or create a new instance if one is not set
	 *
	 * @return \Eve\DI
	 */
	public final function getDI()
	{
		if (null === $this->di) {
			$this->di = new \Eve\DI\FactoryDefault();
		}
		return $this->di;
	}

	public function handle()
	{

	}
}