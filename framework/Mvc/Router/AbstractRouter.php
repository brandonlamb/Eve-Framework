<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @copyright 2012
 * @package Eve\Mvc\Router
 * @version 0.1.0
 */
namespace Eve\Mvc\Router;

// Namespace aliases
use Eve\Mvc as Mvc;

abstract class AbstractRouter extends Mvc\Component implements RouterInterface, \Eve\ResourceInterface {}
