<?php
namespace Eve\Session;

// Namespace aliases
use Eve as Eve;

class Cookie implements DriverInterface
{
	/**
	 * Config array
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * The Crypter instance.
	 *
	 * @var Crypter
	 */
	protected $_crypter;

	/**
	 * Create a new Cookie session driver instance.
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$this->_config = $this->validateConfig($config);
		$this->_crypter = new Eve\Crypter;
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
		if (!isset($config['key'])) { $config['key'] = 'SESSION_COOKIE'; }
		if (!isset($config['lifetime'])) { $config['lifetime'] = 3600; }
		if (!isset($config['path'])) { $config['path'] = '/'; }
		if (!isset($config['domain'])) { $config['domain'] = null; }
		if (!isset($config['https'])) { $config['https'] = false; }
		if (!isset($config['http_only'])) { $config['http_only'] = 3600; }
		return $config;
	}

	/**
	 * Load a session by ID.
	 *
	 * @param  string  $sessionId
	 * @return array
	 */
	public function read($sessionId)
	{
		if (Eve\Cookie::has('session_payload')) {
			return unserialize($this->_crypter->decrypt(Eve\Cookie::get('session_payload')));
		}
		return array();
	}

	/**
	 * Save a session.
	 *
	 * @param  array  $session
	 * @return void
	 */
	public function write($session)
	{
		if (!headers_sent()) {
			extract($this->_config);
			$payload = $this->_crypter->encrypt(serialize($session));
			\Eve\Cookie::put('session_payload', $payload, $lifetime, $path, $domain, $https, $http_only);
		}
	}

	/**
	 * Delete a session by ID.
	 *
	 * @param  string  $sessionId
	 * @return void
	 */
	public function destroy($sessionId)
	{
		Eve\Cookie::forget('session_payload');
	}
}
