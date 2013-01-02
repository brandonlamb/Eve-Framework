<?php
/**
 * Simple logger class based on a similer class created by
 * Darko Bunic (http://www.redips.net/php/write-to-log-file/)
 * Does simple logging to a specified file. See https://bitbucket.org/huntlyc/simple-php-logger for more details.
 *
 * @package default
 * @author Huntly Cameron <huntly.cameron@gmail.com>
 */
namespace Eve;

class Log
{
	/**
	 * @var string, the log file to write to
	 */
	protected $file;

	/**
	 * Constructor
	 * @param string $logfile, - [optional] Absolute file name/path. Defaults to ubuntu apache log.
	 * @return void
	 */
	public function __construct($file = null)
	{
		$this->file = $file;

		// If passed a filename, make sure it is writable
		if (null !== $file && !is_writable($file)) {
			throw new \Exception("LOGGER ERROR: Can't write to log", 1);
		}
	}

	/**
	 * Log Debug
	 * @param string $tag, Log Tag
	 * @param string $message, message to log
	 * @return Log
	 */
	public function debug($tag, $message)
	{
		$this->write('DEBUG', $tag, $message);
		return $this;
	}

	/**
	 * Log Error
	 * @param string $tag, Log Tag
	 * @param string $message, message to log
	 * @return Log
	 */
	public function error($tag, $message)
	{
		$this->write('ERROR', $tag, $message);
		return $this;
	}

	/**
	 * Log Warning
	 * @param string $tag, Log Tag
	 * @param string $message, message to log
	 * @return Log
	 */
	public function warn($tag, $message)
	{
		$this->write('WARNING', $tag, $message);
		return $this;
	}

	/**
	 * Log Info
	 * @param string $tag, Log Tag
	 * @param string $message, message to log
	 * @return Log
	 */
	public function info($tag, $message)
	{
		$this->write('INFO', $tag, $message);
		return $this;
	}

	/**
	 * Common log method with optional level
	 * @param string $tag
	 * @param string $message
	 * @param string $level
	 * @return Log
	 */
	public function log($tag, $message, $level = 'info')
	{
		switch ($level) {
			case 'debug':
				$this->debug($tag, $message);
				break;
			case 'info':
				$this->info($tag, $message);
				break;
			case 'warn':
				$this->warn($tag, $message);
				break;
			case 'error':
				$this->error($tag, $message);
				break;
			default:
				$this->info($tag, $message);
		}

		return $this;
	}

	/**
	 * Writes out timestamped message to the log file as defined by the $file class variable.
	 * @param string status - "INFO"/"DEBUG"/"ERROR" e.t.c.
	 * @param string tag - "Small tag to help find log entries"
	 * @param string message - The message you want to output.
	 * @return void
	 */
	protected function write($status, $tag, $message)
	{
		$message = date('[Y-m-d H:i:s]') . ": [$tag][$status] - $message" . PHP_EOL;

		if (null === $this->file) {
			error_log($message, 0);
		} else {
	        @file_put_contents($this->file, $message, FILE_APPEND | LOCK_EX);
		}
	}
}