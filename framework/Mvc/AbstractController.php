<?php
namespace Eve\Mvc;

abstract class AbstractController extends \Eve\DI\Injectable
{
    public function init() {}
    public function beforeDispatch() {}
    public function afterDispatch() {}
}