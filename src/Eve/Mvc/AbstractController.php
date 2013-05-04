<?php
namespace Eve\Mvc;

use Eve\Di\InjectionAwareInterface,
	Eve\Di\InjectableTrait,
	Eve\Events\EventsAwareInterface;

abstract class AbstractController implements ControllerInterface, InjectionAwareInterface, EventsAwareInterface
{
	use InjectableTrait;

    public function init() {}
    public function beforeDispatch() {}
    public function afterDispatch() {}
}
