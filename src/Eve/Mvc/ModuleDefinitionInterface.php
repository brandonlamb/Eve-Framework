<?php
namespace Eve\Mvc;
use Eve\DiInterface;

interface ModuleDefinitionInterface
{
	public function registerAutoloaders();

	/**
	 * Register DI services
	 *
	 * @param DiInterface $di
	 */
	public function registerServices(DiInterface $di);
}
