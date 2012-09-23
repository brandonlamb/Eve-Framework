<?php

namespace Eve\Model;

abstract class AbstractModel
{
	/**
	 * @var Eve\Cache\CacheInterface
	 */
	protected $cache;

	/**
	 * @var array, model data
	 */
	protected $data = array();

	/**
	 * @var array, changed data fields
	 */
	protected $dataModified = array();

	/**
	 * @var string, Alias for the table to be used in SELECT queries
	 */
	protected $tableAlias;

	/**
	 * @var array, FROM tables
	 */
	protected $tables = array();

	/**
	 * Return defined fields of the entity
	 *
	 * @return array
	 */
	public static function fields()
	{
		return array();
	}
}
