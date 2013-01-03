<?php
namespace Eve\Mvc;
use Eve\DI\InjectableTrait;
use Eve\DI\InjectionAwareInterface;
use Eve\Events\EventsAwareInterface;

abstract class AbstractController implements ControllerInterface, InjectionAwareInterface, EventsAwareInterface
{
	use InjectableTrait;

    public function init() {}
    public function beforeDispatch() {}
    public function afterDispatch() {}
}