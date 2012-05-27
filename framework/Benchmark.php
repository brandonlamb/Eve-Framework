<?php
namespace Eve;

class Benchmark
{
	/**
	 * All of the benchmark starting times.
	 *
	 * @var array
	 */
	public static $marks = array();

	/**
	 * Enforce singleton; disallow instantiation
	 *
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Enforce singleton; disallow cloning
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Start a benchmark.
	 *
	 * After starting a benchmark, the elapsed time in milliseconds can be
	 * retrieved using the "check" method.
	 *
	 * @param string $name, key to use to save in marks
	 * @param mixed $content, optional content to display
	 * @return void
	 * @see	check
	 */
	public static function start($name, $content = null)
	{
		static::$marks[$name] = array(
			'start' => microtime(true),
			'stop' => null,
			'time' => 0,
			'memory' => 0,
			'content' => null !== $content ? $content : null,
		);
	}

	/**
	 * Stop a benchmark.
	 *
	 * After starting a benchmark, the elapsed time in milliseconds can be
	 * retrieved using the "check" method.
	 *
	 * @param string $name, key to use to save in marks
	 * @param mixed $content, optional content to display
	 * @return void
	 * @see	check
	 */
	public static function stop($name, $content = null)
	{
		// Set stop time
		static::$marks[$name]['stop'] = microtime(true);

		// Set time taken between start and stop times
#		$diff = number_format(static::$marks[$name]['stop'] - static::$marks[$name]['start'], 10);
		$diff = number_format((static::$marks[$name]['stop'] - static::$marks[$name]['start']) * 1000, 2);
		static::$marks[$name]['time'] = $diff . 'ms';

		// Get memory used
		static::$marks[$name]['memory'] = number_format(memory_get_usage() / 1024, 2);

		// Set content if any
		static::$marks[$name]['content'] = null !== $content ? $content : null;
	}

	/**
	 * Display entire benchmark log
	 *
	 * @return void
	 */
	/*
	public static function display()
	{
		echo "\n<script>console.log('"
			. static::memory() . 'K / '
			. static::memoryPeak() . 'K / '
			. static::check($name) . ' ms'
			. "');</script>";
	}
	*/

	/**
	 * Get the elapsed time in milliseconds since starting a benchmark.
	 *
	 * @param  string  $name
	 * @return float
	 * @see	start
	 */
	/*
	public static function check($name)
	{
		if (array_key_exists($name, static::$marks)) {
#			return number_format((microtime(true) - static::$marks[$name]) * 1000, 2);
#			return round((microtime(true) - static::$marks[$name]), 5);
			return round((microtime(true) - static::$marks[$name]), 5) . ' / ' .
				number_format((microtime(true) - static::$marks[$name]) * 1000, 2);
		}

		return 0.0;
	}
	*/

	/**
	 * Get the total memory usage in megabytes.
	 *
	 * @return float
	 */
	/*
	public static function memory()
	{
		return number_format(memory_get_usage() / 1024, 2);
	}
	*/

	/**
	 * Get the total memory peak usage in megabytes.
	 *
	 * @return float
	 */
	/*
	public static function memoryPeak()
	{
		return number_format(memory_get_peak_usage() / 1024, 2);
	}
	*/
}
