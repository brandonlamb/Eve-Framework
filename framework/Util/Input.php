<?php
namespace Eve\Util;

// Namespace aliases
use Eve\Util as Util;

class Input
{
	/**
	 * The input data for the request.
	 *
	 * @var array
	 */
	public static $input;

	/**
	 * Get all of the input data for the request.
	 *
	 * This method returns a merged array containing Input::get and Input::file.
	 *
	 * @return array
	 */
	public static function all()
	{
		return array_merge(static::get(), static::file());
	}

	/**
	 * Determine if the input data contains an item.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public static function has($key)
	{
		return (null !== static::get($key) && trim((string) static::get($key)) !== '');
	}

	/**
	 * Get an item from the input data.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return string
	 */
	public static function get($key = null, $default = null)
	{
		if (null === static::$input) { static::hydrate(); }

		return Arrays::get(static::$input, $key, $default);
	}

	/**
	 * Determine if the old input data contains an item.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public static function had($key)
	{
		return (null !== static::old($key) && trim((string) static::old($key)) !== '');
	}

	/**
	 * Get input data from the previous request.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return string
	 */
	public static function old($key = null, $default = null)
	{
		if (\Eve::app()->getComponent('config')->components['session']['driver'] == '') {
			throw new \Exception("Sessions must be enabled to retrieve old input data.");
		}

		return Arrays::get(Eve\Session::get('sf_old_input', array()), $key, $default);
	}

	/**
	 * Get an item from the uploaded file data.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return array
	 */
	public static function file($key = null, $default = null)
	{
		return Arrays::get($_FILES, $key, $default);
	}

	/**
	 * Hydrate the input data for the request.
	 *
	 * @return void
	 */
	public static function hydrate()
	{
		switch (\Eve::app()->getComponent('request')->method()) {
			case 'GET':
				static::$input =& $_GET;
				break;

			case 'POST':
				static::$input =& $_POST;
				break;

			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents('php://input'), static::$input);
		}
	}
}
