<?php
namespace Eve\Mvc;
use Eve\DI\InjectionAwareInterface;
use Eve\Events\EventsAwareInterface;

abstract class AbstractController implements ControllerInterface, InjectionAwareInterface, EventsAwareInterface
{
	use \Eve\DI\InjectableTrait;

    public function init() {}
    public function beforeDispatch() {}
    public function afterDispatch() {}
}