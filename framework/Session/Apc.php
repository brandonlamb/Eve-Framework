<?php
namespace Eve\Session;

// Namespace aliases
use Eve as Eve;

class Apc implements DriverInterface
{
	/**
	 * Config array
	 *
	 * @var array
	 */
	protected $_config = array();

	public function __construct($config = array())
	{
		$this->_config = $this->validateConfig($config);
	}

	/**
	 * Validate require config options
	 *
	 * @param array $config
	 * @return array
	 */
	public function validateConfig(array $config)
	{
		// Verify required options
		if (!isset($config['lifetime'])) { $config['lifetime'] = 3600; }
		return $config;
	}

	/**
	 * Load a session by ID.
	 *
	 * @param string $sessionId
	 * @return array
	 */
	public function read($sessionId)
	{
		return Eve\Cache::driver('apc')->get($sessionId);
	}

	/**
	 * Save a session.
	 *
	 * @param array $session
	 * @return void
	 */
	public function write($session)
	{
		Eve\Cache::driver('apc')->put($session['id'], $session, $this->_config['lifetime']);
	}

	/**
	 * Delete a session by ID.
	 *
	 * @param string $sessionId
	 * @return void
	 */
	public function destroy($sessionId)
	{
		Eve\Cache::driver('apc')->forget($sessionId);
	}
}
