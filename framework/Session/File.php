<?php
namespace Eve\Session;

class File implements DriverInterface, SweeperInterface
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
		if (!isset($config['prefix'])) { $config['prefix'] = null; }
		if (!isset($config['sessionPath'])) { $config['sessionPath'] = '/tmp'; }
		if (!isset($config['lifetime'])) { $config['lifetime'] = 3600; }
		return $config;
	}

	/**
	 * Return path by id
	 *
	 * @param string $sessionId
	 * @return string
	 */
	public function getPath($sessionId)
	{
		return $this->_config['sessionPath'] . DIRECTORY_SEPARATOR . $this->_config['prefix'] . $sessionId;
	}

	/**
	 * Load a session by ID.
	 *
	 * @param  string  $sessionId
	 * @return array
	 */
	public function read($sessionId)
	{
		$path = $this->getPath($sessionId);
		return ($resourceAbsolutePath = stream_resolve_include_path($path)) ?
			unserialize(file_get_contents($resourceAbsolutePath)) : null;
	}

	/**
	 * Write session data to storage
	 *
	 * @param array $session
	 * @return void
	 */
	public function write($session)
	{
		$path = $this->getPath($session['id']);
		file_put_contents($path, serialize($session), LOCK_EX);
	}

	/**
	 * Destroy session data by session_id.
	 *
	 * @param string $sessionId
	 * @return void
	 */
	public function destroy($sessionId)
	{
		@unlink($this->getPath($sessionId));
	}

	/**
	 * Perform garbage collection on all expired sessions
	 *
	 * @param int $expiration
	 * @return void
	 */
	public function garbageCollect($expiration)
	{
		$pattern = $this->_config['sessionPath'] . DIRECTORY_SEPARATOR . $this->_config['prefix'] . '*';

		foreach (glob($pattern) as $file) {
			if (filetype($file) == 'file' && filemtime($file) < $expiration) { @unlink($file); }
		}
	}
}
