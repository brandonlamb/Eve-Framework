<?php
namespace Eve\DI;

class FactoryDefault extends \Eve\DI
{
    public function __construct($config = null)
    {
        parent::__construct($config);

        $this->set('dispatcher', '\\Eve\\Mvc\\Dispatcher');
#        $this->set('router', '\\Eve\Mvc\\Router');
        $this->set('response', '\\Eve\\Mvc\\Response');
        $this->set('request', '\\Eve\\Http\\Request');

        $this->set('logger', function () {
        	return new \Eve\Log(\PATH . '/protected/logs/application-' . strftime('%Y-%m-%d') . '.log');
        });

        $this->set('loader', function () {
        	$loader = new \Eve\Loader();
        	$loader->register();
        	return $loader;
        });

        $this->set('router', function () {
			$router = new \Eve\Mvc\Router();
			$router->map('/:controller/:action/:params', array('module' => 'default'), array(
				'filters' => array(
					'params' => '?(.*)?',
				)
			));

			return $router;
        });

        $this->set('view', function() {
			return new \Eve\Mvc\View();
		});
    }
}
