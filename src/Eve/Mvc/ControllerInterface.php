<?php
namespace Eve\Mvc;

interface ControllerInterface
{
	public function init();
	public function beforeDispatch();
	public function afterDispatch();
}