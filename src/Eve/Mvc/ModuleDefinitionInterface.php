<?php
namespace Eve\Mvc;

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
