<?php
require_once dirname(__FILE__) . '/../protected/vendors/Eve/Benchmark.php';
Eve\Benchmark::start('main');

// Set constants
define('DOCROOT', dirname(__FILE__));
define('PATH', dirname(DOCROOT));

// Get app class
require_once \PATH . '/protected/vendors/Eve/Eve.php';

// Run application
Eve::init(\PATH . '/protected/config/main.php')->run();

Eve\Benchmark::stop('main');
