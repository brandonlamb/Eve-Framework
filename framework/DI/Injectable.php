<?php
namespace Eve\DI;

abstract class Injectable implements InjectableInterface
{
	/**
	 * @var DI
	 */
	protected $di;

	public function __construct(\Eve\DI $di = null)
	{
		if (null === $di) {
			$di = \Eve\DI::getDefault();
		}
		$this->di = $di;
	}

	/**
	 * Set the DI container
	 *
	 * @param DI $di
	 * @return Injectable
	 */
	public function setDI(\Eve\DI $di)
	{
		$this->di = $di;
		return $this;
	}

	/**
	 * Return the DI container
	 *
	 * @return DI
	 */
	public function getDI()
	{
		return $this->di;
	}
}