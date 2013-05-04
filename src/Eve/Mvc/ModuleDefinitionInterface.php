<?php
namespace Eve\Mvc;
use Eve\Di\DiInterface;

interface ModuleDefinitionInterface
{
	/**
	 * Register autoloaders
	 *
	 * @param DiInterface $di
	 */
	public function registerAutoloaders(DiInterface $di);

	/**
	 * Register DI services
	 *
	 * @param DiInterface $di
	 */
	public function registerServices(DiInterface $di);
}
