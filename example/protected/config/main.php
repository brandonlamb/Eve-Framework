<?php

// System Start Memory
define('START_MEMORY_USAGE', memory_get_usage());

// Set error reporting options
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', true);
ini_set('display_errors', true);

// Directory separator (Unix-Style works on all OS)
define('DS', DIRECTORY_SEPARATOR);

// The current TLD address, scheme, and port
define('DOMAIN', (strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'
	. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));


// Default timezone of server
date_default_timezone_set('UTC');

// iconv encoding
iconv_set_encoding('internal_encoding', 'UTF-8');

// multibyte encoding
mb_internal_encoding('UTF-8');

// Sort cache options
#$caches = (function_exists('xcache_isset')) ? array('class' => 'CXCache') : array('class' => 'CFileCache');
#define('SITE_DOMAIN', '');

// Main config
return array(
	'basePath'		=> \PATH . '/protected',
	'viewPath'		=> \PATH . '/protected/modules',
	'modulesPath'	=> \PATH . '/protected/modules',
	'vendorsPath'	=> \PATH . '/protected/vendors',
	'runtimePath'	=> \PATH . '/runtime',
	'charset'		=> 'UTF-8',
	'layout'		=> 'default',
	'name'			=> 'My Site',

	// Components
	'components' => array(
		// Import autoload components
		'autoloader' => array(
			'class' => '\Eve\Mvc\SplClassLoader',
			'file' => \PATH . '/protected/vendors/Eve/Mvc/SplClassLoader.php',
			'ns' => array(
				'Eve'	=> array(\PATH . '/protected/vendors'),
				'Zend'	=> array(\PATH . '/protected/vendors'),
				'Main'	=> array(\PATH . '/protected/modules'),
				'Api'	=> array(\PATH . '/protected/modules'),
			),
		),

		// Configuration component settings
		'config' => array(),

		// Encryption component settings
		'crypter' => array(
			'class' => '\Eve\Crypter',
			'cipher' => 'rijndael-256',
			'mode' => 'cbc',
			'key' => 'abc123',
		),

		// Database component
		'db' => array(
			'class'		=> 'Eve\Database',
			'connections' => array(
				'rw' => array(
					'driver'	=> 'Pdo',
					'dsn'		=> 'mysql:host=localhost;dbname=mydb',
					'username'	=> 'developer',
					'password'	=> 'mypass',
				),
			),
		),

		// Dispatcher component settings
		'dispatcher' => array(
			'class' => '\Eve\Mvc\Dispatcher',
			'routers' => array(),
		),

		// Error component settings
		'errorHandler' => array(
			'class' => 'Eve\Mvc\Error',
			'display' => true,
			'template' => '/protected/modules/main/layouts/fatalerror.php',
		),

		// Event component settings
		'event' => array(
			'class' => '\Eve\Mvc\Event',
			'events' => array(),
		),

		// Request component settings
		'request' => array(
			'class' => '\Eve\Mvc\Request',
			'default' => array(
				'module' => 'main',
				'controller' => 'index',
				'action' => 'index',
			),

			'error' => array(
				'controller' => 'error',
				'action' => 'index',
				'exception' => 'exception',
				'notFound' => 'not-found',
			),
		),

		// Response component settings
		'response' => array(
			'class' => '\Eve\Mvc\Response',
		),

		// Session component settings
		'session' => array(
			'class' => 'Eve\Session',
			'driver' => 'file',
			'options' => array(
				'cookie' => array(
					'lifetime' => 120,
				),
				'file' => array(
					'lifetime' => 60,
					'prefix' => 'Session_',
					'sessionPath' => \PATH . '/runtime/sessions',
				),
			),
		),

	),

	// Modules
	'modules' => array(
		'api' => array(
			'default' => array(
				'controller' => 'index',
				'action' => 'index',
				'layout' => 'json',
			),
			'error' => array(
				'controller' => 'error',
				'layout' => 'json',
				'notFound' => 'not-found',
				'exception' => 'exception',
			),
		),
		'main' => array(
			'default' => array(
				'controller' => 'index',
				'action' => 'index',
				'layout' => 'default',
			),
			'error' => array(
				'controller' => 'error',
				'layout' => 'default',
				'notFound' => 'not-found',
				'exception' => 'exception',
			),
		),
	),

	// Preload components
	'preload' => array(
		'errorHandler',
#		'session',
	),
);

/**
 * Debugging function, die after output
 */
function d()
{
	$string = '';
	foreach(func_get_args() as $value)
	{
		$string .= '<pre>';
		$string .= $value === NULL ? 'NULL' : (is_scalar($value) ? $value : print_r($value, TRUE));
		$string .= "</pre>\n";
	}
	echo $string;
	\Eve::shutdown();
	die;
}

/**
 * Debug function, dont die after output
 */
function dump()
{
	$string = '';
	foreach(func_get_args() as $value)
	{
		$string .= '<pre>';
		$string .= $value === NULL ? 'NULL' : (is_scalar($value) ? $value : print_r($value, TRUE));
		$string .= "</pre>\n";
	}
	echo $string;
}
