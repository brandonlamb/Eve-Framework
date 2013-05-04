<?php

namespace Eve\Session;

class SessionWrapper
{
	const FLASH_KEY = 'SessionFlashes';

	public $started = false;
	private $ssid;
	private $initime;
	private $useragent;
	private $clientip;

	public function __construct()
	{
		if (session_status() !== \PHP_SESSION_ACTIVE) {
			session_start();
		}

		$this->started = true;
		$this->ssid = session_id();
		$this->initime = time();
		$this->useragent = $_SERVER['HTTP_USER_AGENT'];
		$this->clientip = $_SERVER['REMOTE_ADDR'];

		!isset($_SESSION[static::FLASH_KEY]) && $_SESSION[static::FLASH_KEY] = [];
	}

	/**
	 * Magic getter
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function & __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Magic setter
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Return session id
	 * @throws \Exception
	 */
	public function id()
	{
		$this->validate();

		if (empty($this->ssid)) {
			throw new \Exception('Session already expired...');
		}
		return $this->ssid;
	}

	/**
	 * Check if session key exists
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		$this->validate();
		return !empty($_SESSION[$key]) ? true : false;
	}

	/**
	 * Get a session key, with optional return value if key is not set
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function & get($key = null, $default = null)
	{
		$this->validate();

		if (null === $key) {
			return $_SESSION;
		}

		if ($this->has($key)) {
			return $_SESSION[$key];
		} else {
			return $default;
		}
	}

	/**
	 * Set session key
	 * @param string $key
	 * @param mixed $value
	 * @param bool $override
	 * @param bool $encrypt
	 */
	public function set($key, $value, $override = true, $encrypt = false)
	{
		$this->validate();
		$value = ($encrypt === true) ? hash('sha512', $value) : $value;

		if ($this->has($key) && $override === false) {
			throw new \Exception('Cannot override session var ' . $key . '.');
		}

		$_SESSION[$key] = $value;
	}

	/**
	 * Delete session key
	 * @param string $key
	 */
	public function delete($key)
	{
		$this->validate();
		if ($this->has($key)) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Regenerate session id
	 */
	public function regen()
	{
		$this->validate();
		$this->ssid = session_regenerate_id(true);
		$this->initime = time();
	}

	/**
	 * Verify user agent and ip have not changed
	 * @throws \Exception
	 */
	private function validate()
	{
		if ($this->useragent != $_SERVER['HTTP_USER_AGENT'] || $this->clientip != $_SERVER['REMOTE_ADDR']) {
			$this->destroy();
			throw new \Exception('User IP has changed...');
		}
	}

	/**
	 * Destroy session
	 */
	public function destroy()
	{
		$_SESSION = [];
		session_destroy();
	}
}
