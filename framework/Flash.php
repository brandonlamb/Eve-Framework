<?php
/**
 * Session flash class. This class handles setting one-off flash messages for the user
 *
 * @todo Fix short variable names like $n
 */
namespace Eve;

// Namespace aliases
use Eve\Mvc as Mvc;

class Flash extends Mvc\Component
{
	const FLASH_KEY_PREFIX = 'Eve.flash.';
	const FLASH_COUNTERS = 'Eve.flashcounters';
	const STATES_VAR = '__states';
	const AUTH_TIMEOUT_VAR = '__timeout';

	/**
	 * @var boolean whether to automatically update the validity of flash messages.
	 * Defaults to true, meaning flash messages will be valid only in the current and the next requests.
	 * If this is set false, you will be responsible for ensuring a flash message is deleted after usage.
	 * (This can be achieved by calling {@link getFlash} with the 3rd parameter being true).
	 * @since 1.1.7
	 */
	public $autoUpdateFlash = true;

	private $_keyPrefix;
#	private $_access = array();

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can be accessed like properties.
	 * @param string $name property name
	 * @return mixed property value
	 */
	public function __get($name)
	{
		if ($this->hasState($name)) {
			return $this->getState($name);
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can be set like properties.
	 * @param string $name property name
	 * @param mixed $value property value
	 */
	public function __set($name, $value)
	{
		if ($this->hasState($name)) {
			$this->setState($name,$value);
		} else {
			parent::__set($name,$value);
		}
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can also be checked for null value.
	 * @param string $name property name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if ($this->hasState($name)) {
			return $this->getState($name) !== null;
		} else {
			return parent::__isset($name);
		}
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can also be unset.
	 * @param string $name property name
	 * @throws CException if the property is read only.
	 */
	public function __unset($name)
	{
		if ($this->hasState($name)) {
			$this->setState($name,null);
		} else {
			parent::__unset($name);
		}
	}

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by starting session,
	 * performing cookie-based authentication if enabled, and updating the flash variables.
	 */
	public function init()
	{
		if ($this->autoUpdateFlash) { $this->updateFlash(); }
	}

	/**
	 * @return mixed the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function getId()
	{
		return $this->getState('__id');
	}

	/**
	 * @param mixed $value the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function setId($value)
	{
		$this->setState('__id', $value);
	}

	/**
	 * Returns the unique identifier for the user (e.g. username).
	 * This is the unique identifier that is mainly used for display purpose.
	 * @return string the user name. If the user is not logged in, this will be {@link guestName}.
	 */
	public function getName()
	{
		if (($name=$this->getState('__name')) !== null) {
			return $name;
		} else {
			return $this->guestName;
		}
	}

	/**
	 * Sets the unique identifier for the user (e.g. username).
	 * @param string $value the user name.
	 * @see getName
	 */
	public function setName($value)
	{
		$this->setState('__name', $value);
	}


	/**
	 * @return string a prefix for the name of the session variables storing user session data.
	 */
	public function getStateKeyPrefix()
	{
		if ($this->_keyPrefix !== null) {
			return $this->_keyPrefix;
		} else {
			return $this->_keyPrefix = md5('Eve.' . get_class($this) . '.');
		}
	}

	/**
	 * @param string $value a prefix for the name of the session variables storing user session data.
	 */
	public function setStateKeyPrefix($value)
	{
		$this->_keyPrefix = $value;
	}

	/**
	 * Returns the value of a variable that is stored in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * A variable, if stored in user session using {@link setState} can be
	 * retrieved back using this function.
	 *
	 * @param string $key variable name
	 * @param mixed $defaultValue default value
	 * @return mixed the value of the variable. If it doesn't exist in the session,
	 * the provided default value will be returned
	 * @see setState
	 */
	public function getState($key,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
	}

	/**
	 * Stores a variable in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * By storing a variable using this function, the variable may be retrieved
	 * back later using {@link getState}. The variable will be persistent
	 * across page requests during a user session.
	 *
	 * @param string $key variable name
	 * @param mixed $value variable value
	 * @param mixed $defaultValue default value. If $value===$defaultValue, the variable will be
	 * removed from the session
	 * @see getState
	 */
	public function setState($key, $value, $defaultValue = null)
	{
		$key = $this->getStateKeyPrefix() . $key;
		if ($value === $defaultValue) {
			unset($_SESSION[$key]);
		} else {
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * Returns a value indicating whether there is a state of the specified name.
	 * @param string $key state name
	 * @return boolean whether there is a state of the specified name.
	 */
	public function hasState($key)
	{
		$key = $this->getStateKeyPrefix() . $key;
		return isset($_SESSION[$key]);
	}

	/**
	 * Clears all user identity information from persistent storage.
	 * This will remove the data stored via {@link setState}.
	 */
	public function clearStates()
	{
		$keys = array_keys($_SESSION);
		$prefix = $this->getStateKeyPrefix();
		$n = strlen($prefix);
		foreach ($keys as $key) {
			if (!strncmp($key, $prefix, $n)) { unset($_SESSION[$key]); }
		}
	}

	/**
	 * Returns all flash messages.
	 * This method is similar to {@link getFlash} except that it returns all
	 * currently available flash messages.
	 * @param boolean $delete whether to delete the flash messages after calling this method.
	 * @return array flash messages (key => message).
	 * @since 1.1.3
	 */
	public function getFlashes($delete = true)
	{
		$flashes = array();
		$prefix = $this->getStateKeyPrefix() . self::FLASH_KEY_PREFIX;
		$keys = array_keys($_SESSION);
		$n = strlen($prefix);
		foreach ($keys as $key) {
			if (!strncmp($key, $prefix, $n)) {
				$flashes[substr($key, $n)] = $_SESSION[$key];
				if ($delete) { unset($_SESSION[$key]); }
			}
		}

		if ($delete) {
			$this->setState(self::FLASH_COUNTERS,array());
		}
		return $flashes;
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message is not available.
	 * @param boolean $delete whether to delete this flash message after accessing it.
	 * Defaults to true.
	 * @return mixed the message message
	 */
	public function getFlash($key, $defaultValue = null, $delete = true)
	{
		$value = $this->getState(self::FLASH_KEY_PREFIX . $key, $defaultValue);
		if ($delete) {
			$this->setFlash($key,null);
		}
		return $value;
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 */
	public function setFlash($key, $value, $defaultValue = null)
	{
		$this->setState(self::FLASH_KEY_PREFIX . $key, $value, $defaultValue);
		$counters = $this->getState(self::FLASH_COUNTERS, array());
		if ($value === $defaultValue) {
			unset($counters[$key]);
		} else {
			$counters[$key] = 0;
		}
		$this->setState(self::FLASH_COUNTERS, $counters, array());
	}

	/**
	 * @param string $key key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash($key)
	{
		return $this->getFlash($key, null, false) !== null;
	}

	/**
	 * Retrieves identity states from persistent storage and saves them as an array.
	 * @return array the identity states
	 */
	protected function saveIdentityStates()
	{
		$states = array();
		foreach ($this->getState(self::STATES_VAR, array()) as $name => $dummy) {
			$states[$name]=$this->getState($name);
		}
		return $states;
	}

	/**
	 * Loads identity states from an array and saves them to persistent storage.
	 * @param array $states the identity states
	 */
	protected function loadIdentityStates($states)
	{
		$names = array();
		if (is_array($states)) {
			foreach ($states as $name => $value) {
				$this->setState($name, $value);
				$names[$name] = true;
			}
		}
		$this->setState(self::STATES_VAR, $names);
	}

	/**
	 * Updates the internal counters for flash messages.
	 * This method is internally used by {@link CWebApplication}
	 * to maintain the availability of flash messages.
	 */
	protected function updateFlash()
	{
		$counters=$this->getState(self::FLASH_COUNTERS);
		if (!is_array($counters)) { return; }
		foreach ($counters as $key => $count) {
			if ($count) {
				unset($counters[$key]);
				$this->setState(self::FLASH_KEY_PREFIX . $key, null);
			} else {
				$counters[$key]++;
			}
		}
		$this->setState(self::FLASH_COUNTERS, $counters, array());
	}
}
