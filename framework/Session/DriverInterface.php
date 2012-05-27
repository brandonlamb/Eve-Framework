<?php
namespace Eve\Session;

interface DriverInterface
{
	/**
	 * Load a session by ID.
	 *
	 * @param string $sessionId
	 * @return array
	 */
	public function read($sessionId);

	/**
	 * Save a session.
	 *
	 * @param array $session
	 * @return void
	 */
	public function write($session);

	/**
	 * Delete a session by ID.
	 *
	 * @param string $sessionId
	 * @return void
	 */
	public function destroy($sessionId);

	/**
	 * Validate config options for individual driver
	 *
	 * @param array $config
	 * @return array
	 */
	public function validateConfig(array $config);
}
