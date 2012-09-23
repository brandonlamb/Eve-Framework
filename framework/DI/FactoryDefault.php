<?php
namespace Eve\DI;

class FactoryDefault extends \Eve\DI
{
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->set('dispatcher', '\\Eve\\Mvc\\Dispatcher');
	}
}