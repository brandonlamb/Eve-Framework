<?php
namespace Eve\Mvc;

abstract class Controller extends \Eve\DI\Injectable
{
    public function init() {}
    public function beforeDispatch() {}
    public function afterDispatch() {}
}