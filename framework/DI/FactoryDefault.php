<?php
namespace Eve\DI;

class FactoryDefault extends \Eve\DI
{
    public function __construct($config = null)
    {
        parent::__construct($config);

        $this->set('config', '\\Eve\\Mvc\\Config');
        $this->set('dispatcher', '\\Eve\\Mvc\\Dispatcher');
        $this->set('logger', '\\Eve\\Log');
        $this->set('response', '\\Eve\\Http\\Response');
        $this->set('request', '\\Eve\\Http\\Request');
        $this->set('router', '\\Eve\\Mvc\\Router');
        $this->set('view', '\\Eve\\Mvc\\View');

        $this->set('loader', function () {
        	$loader = new \Eve\Loader();
        	$loader->register();
        	return $loader;
        });
    }
}
