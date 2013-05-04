<?php
namespace Eve\Di;

use Eve\Di\Container;

class FactoryDefault extends Container
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
        $this->set('session', '\\Eve\\Session\\SessionWrapper');
        $this->set('view', '\\Eve\\Mvc\\View');

        $this->set('loader', function () {
        	$loader = new \Eve\Loader();
        	$loader->register();
        	return $loader;
        });
    }
}
