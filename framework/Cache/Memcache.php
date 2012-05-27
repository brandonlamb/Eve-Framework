<?php
/**
 * Eve Application Framework
 *
 * @author    Phil Bayfield
 * @copyright 2010
 * @license   Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package   Eve\Cache
 * @version   0.1.0
 */
namespace Eve\Cache;

// Namespace aliases
use Eve\App as App;
use Eve\Cache\Memcache as Memcache;

class Memcache extends \Memcache
{
	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_CONFIG = 'config';

	/**
	 * Constructor
	 *
	 * @param mixed $servers
	 * @throws MemcacheException
	 * @return void
	 */
	public function __construct($servers)
	{
		// Add specified servers to pool
		if (is_array($servers)) {
			foreach ($servers as $server) {
				$this->_addServerFromString($server);
			}
		} elseif (is_string($servers)) {
			$this->_addServerFromString($servers);
		} else {
			throw new MemcacheException(
				'Unknown server type specified, must be an array or a string.'
			);
		}
	}

	/**
	 * Add server from supplied string
	 *
	 * @param string $server
	 * @throws MemcacheException
	 * @return bool
	 */
	private function _addServerFromString($server)
	{
		list ($host, $port) = explode(':', $server);
		$res = parent::addServer($host, $port);
		if ($res === false) {
			throw new MemcacheException(
				'There was an error adding server ' . $server . '.'
			);
		}
		return $res;
	}

}