<?php
namespace Eve;

// Namespace aliases
use Eve\Mvc as Mvc;

class Crypter extends Mvc\Component
{
	/**
	 * Create a new Crypter instance.
	 *
	 * @param string $cipher
	 * @param string $mode
	 * @return Crypter
	 */
	public static function make($cipher = 'rijndael-256', $mode = 'cbc')
	{
		return new static(array('cipher' => $cipher, 'mode' => $mode));
	}

	/**
	 * Get the random number source available to the OS.
	 *
	 * @return int
	 */
	public static function getRandomizer()
	{
		if (defined('MCRYPT_DEV_URANDOM')) {
			return MCRYPT_DEV_URANDOM;
		} else if (defined('MCRYPT_DEV_RANDOM')) {
			return MCRYPT_DEV_RANDOM;
		}

		return MCRYPT_RAND;
	}

	/**
	 * Encrypt a value using the MCrypt library.
	 *
	 * @param string $value
	 * @return string
	 */
	public function encrypt($value)
	{
		$cryptIv = mcrypt_create_iv($this->_ivSize(), static::getRandomizer());
		return base64_encode($cryptIv . mcrypt_encrypt($this->_config['cipher'],
			$this->_config['key'], $value, $this->_config['mode'], $cryptIv));
	}

	/**
	 * Decrypt a value using the MCrypt library.
	 *
	 * @param string $value
	 * @return string
	 */
	public function decrypt($value)
	{
		if (null === $value || !is_string($value = base64_decode($value, true))) {
			throw new Exception('Decryption error. Input value is not valid base64 data. "' . $value . '"');
		}
		list($cryptIv, $value) = array(substr($value, 0, $this->_ivSize()), substr($value, $this->_ivSize()));
		return rtrim(mcrypt_decrypt(
			$this->_config['cipher'],
			$this->_config['key'],
			$value,
			$this->_config['mode'],
			$cryptIv
		), "\0");
	}

	/**
	 * Get the input vector size for the cipher and mode.
	 * Different ciphers and modes use varying lengths of input vectors.
	 *
	 * @return int
	 */
	protected function _ivSize()
	{
		return mcrypt_get_iv_size($this->_config['cipher'], $this->_config['mode']);
	}
}
