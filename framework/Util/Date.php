<?php
namespace Eve\Util;

// Namespace aliases
use Eve\Util as Util;

/**
 * Extension of PHP DateTime Class
 * Easy to remember format strings, magic toString method, diff method, and getAge method
 */
class Date extends \DateTime
{
	// Helpful date/time formats
	protected $_formats = array(
		'datetime'	=> 'Y-m-d H:i:s',
		'date'		=> 'Y-m-d',
		'time'		=> 'g:i a',
		'12h'		=> 'g:i a',
		'24h'		=> 'H:i',
		'24hs'		=> 'H:i:s',
		'long'		=> 'l, F j, Y',
		'full'		=> 'l, F j, Y',
		'abbr'		=> 'M j',
		'short'		=> 'm/d/y',
		'unix'		=> 'U',
		'atom'		=> 'r'
	);

	/**
	 * Return Date in MySQL format
	 *
	 * @return String
	 */
	public function __toString()
	{
		return $this->format('datetime');
	}

	/**
	 * Return difference between $this and $now
	 *
	 * @param Datetime|String $now
	 * @return int
	 */
	public function diff($now = 'NOW', $absolute = false)
	{
		if (!$now instanceOf DateTime) {
			$now = new DateTime($now);
		}
		return parent::diff($now);
	}

	/**
	 * Return Age in Years
	 *
	 * @param Datetime|String $now
	 * @return Integer
	 */
	public function getAge($now = 'NOW')
	{
		return $this->diff($now)->format('%y');
	}

	/**
	 * Overloaded function to include extra formats
	 *
	 * @param String $format
	 * @return String
	 */
	public function format($format = 'U')
	{
		if (array_key_exists($format, $this->_formats)) {
			$format = $this->_formats[$format];
		}
		return parent::format($format);
	}

	/**
	 * Static function for printing a formatted date
	 *
	 * @param String $date
	 * @param String $format
	 * @return String
	 */
	public static function get($format = 'U', $date = 'NOW')
	{
		$date = new self($date);
		return $date->format($format);
	}
}
