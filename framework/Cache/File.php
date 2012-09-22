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

class File implements \Eve\ResourceInterface
{
	/**
	 * @var string, cache path
	 */
	private $path;

	/**
	 * @var int, encoding flags
	 */
	const ENC_NONE = 0;
	const ENC_SERIALIZE = 1;
	const ENC_JSON_ARRAY = 2;
	const ENC_JSON_OBJECT = 3;

	/**
	 * Constructor
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Get a value from file cache
	 *
	 * @param string $key
	 * @return bool
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function get($key)
	{
		// Check if file exists
		if ($resourceAbsolutePath = stream_resolve_include_path($this->path . '/' . $key)) {
			// Read file data
			$file = fopen($resourceAbsolutePath, 'r');
			if ($file === false) {
				throw new \RuntimeException('Unable to open the input file.');
			}

			// Read header
			$header = fgets($file);
			if (strpos($header, '::') === false) {
				throw new \RuntimeException('Unable to read from the input file, bad header.');
			}
			list ($expire, $flag) = explode('::', $header);
			if ($expire < time()) {
				return false;
			}

			// Read data
			if (filesize($resourceAbsolutePath) - strlen($header) > 0) {
				$body = fread($file, filesize($resourceAbsolutePath) - strlen($header));
			} else {
				$body = null;
			}

			switch ($flag) {
				default:
				case self::ENC_NONE:
					return $body;
					break;
				case self::ENC_SERIALIZE:
					return unserialize($body);
					break;
				case self::ENC_JSON_ARRAY:
					return json_decode($body, true);
					break;
				case self::ENC_JSON_OBJECT:
					return json_decode($body);
					break;
			}
			fclose($file);
		}
		return false;
	}

	/**
	 * Set a value to file cache
	 *
	 * @param string $key
	 * @param mixed  $var
	 * @param int    $flag (this emulates memcache but we will use it for the encoding method)
	 * @param int    $expire
	 *
	 * @return bool
	 */
	public function set($key, $var, $flag, $expire)
	{
		// Create file
		$resourceAbsolutePath = $this->path . '/' . $key;
		if (($file = fopen($resourceAbsolutePath, 'w')) === false) {
			throw new \RuntimeException('Unable to open output file ' . $resourceAbsolutePath);
		}

		// Write header
		$header = ($expire > time() ? $expire : $expire + time()) . '::' . $flag . \PHP_EOL;
		$res = fwrite($file, $header);
		if ($res === false) {
			throw new \RuntimeException('Unable to write to the output file.');
		}

		// Write data
		switch ($flag) {
			case self::ENC_NONE:
				$data = $var;
				break;
			case self::ENC_SERIALIZE:
				$data = serialize($var);
				break;
			case self::ENC_JSON_ARRAY:
			case self::ENC_JSON_OBJECT:
				$data = json_encode($var);
				break;
			default:
		}

		$res = fwrite($file, $data);
		if ($res === false) {
			throw new \RuntimeException('Unable to write to the output file.');
		}

		fclose($file);
		return true;
	}

	/**
	 * Clean up any expired cache files
	 *
	 * @param string $prefix
	 * @param int    $expire
	 * @param int    $limit
	 */
	public function clean($prefix = null, $expire = 0, $limit = 0)
	{
		// Set counter
		$counter = 0;
		// Iterate over directory
		foreach (new \DirectoryIterator($this->path) as $file) {
			// Check file is valid and correct prefix
			if ($file->isFile() && null !== $prefix	|| substr($file->getFilename(), 0, strlen($prefix)) == $prefix) {
				// Increment counter
				$counter++;
				if ($limit && $counter > $limit) {
					return;
				}

				// Rough check with mtime
				if ($file->getMTime() < time() - $expire) {
					// Open and read header
					$file = fopen($file->getPathname(), 'r');
					if ($file === false) {
						continue;
					}
					$header = fgets($file);
					if (strpos($header, '::') === false) {
						continue;
					}
					list ($expiry, $flag) = explode('::', $header);

					// Unlink if expired
					if ($expiry < time()) {
						unlink($file->getPathname());
					}
					fclose($file);
				}
			}
		}
	}

}
