<?php
/*
// System Start Time
define('START_TIME', microtime(true));

// Set constants
define('PATH', realpath(dirname(__FILE__) . '/../framework'));

require __DIR__ . '/../framework/Eve.php';
require __DIR__ . '/../framework/Mvc/SplAutoLoader.php';
require __DIR__ . '/../framework/Mvc/SplClassLoader.php';

// Configure the SplClassLoader to act normally or silently
$loader = new Eve\Mvc\SplClassLoader();
$loader->setMode(\Eve\Mvc\SplClassLoader::MODE_SILENT);

// Allow to PHP use the include_path for file path lookup
$loader->setIncludePathLookup(true);

// Register the autoloader, prepending it in the stack
$loader->register(false);

try {
	// Run application
	\Eve::init(\PATH . '/protected/config/main.php')->run();
	\Eve::shutdown();
} catch (\Exception $e) {
	d('ROOT', $e);
	\Eve\Mvc\Error::exception($e);
}
*/

require __DIR__ . '/autoload-src.php';
require __DIR__ . '/autoload.php';
