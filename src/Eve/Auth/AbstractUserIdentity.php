<?php
/**
 * AbstractBaseUserIdentity is a base class implementing {@link UserIdentityInterface}.
 *
 * AbstractBaseUserIdentity implements the scheme for representing identity
 * information that needs to be persisted. It also provides the way
 * to represent the authentication errors.
 *
 * Derived classes should implement {@link UserIdentityInterface::authenticate}
 * and {@link UserIdentityInterface::getId} that are required by the {@link UserIdentityInterface}
 * interface.
 *
 * @property mixed $id A value that uniquely represents the identity (e.g. primary key value).
 * The default implementation simply returns {@link name}.
 * @property string $name The display name for the identity.
 * The default implementation simply returns empty string.
 * @property array $persistentStates The identity states that should be persisted.
 * @property whether $isAuthenticated The authentication is successful.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: AbstractBaseUserIdentity.php 3515 2011-12-28 12:29:24Z mdomba $
 * @package system.web.auth
 * @since 1.0
 */
namespace Eve\Auth;

use Eve\Mvc;

abstract class AbstractUserIdentity extends Mvc\Component implements UserIdentityInterface
{
    const ERROR_NONE = 0;
    const ERROR_USERNAME_INVALID = 1;
    const ERROR_PASSWORD_INVALID = 2;
    const ERROR_UNKNOWN_IDENTITY = 100;

    /**
     * @var integer the authentication error code. If there is an error, the error code will be non-zero.
     * Defaults to 100, meaning unknown identity. Calling {@link authenticate} will change this value.
     */
    public $errorCode = self::ERROR_UNKNOWN_IDENTITY;

    /**
     * @var string the authentication error message. Defaults to empty.
     */
    public $errorMessage = '';

    private $state = array();

    /**
     * Returns a value that uniquely represents the identity.
     * The default implementation simply returns {@link name}.
     *
     * @return mixed a value that uniquely represents the identity (e.g. primary key value).
     */
    public function getId()
    {
        return $this->getName();
    }

    /**
     * Returns the display name for the identity (e.g. username).
     * The default implementation simply returns empty string.
     *
     * @return string the display name for the identity.
     */
    public function getName()
    {
        return '';
    }

    /**
     * Returns the identity states that should be persisted.
     * This method is required by {@link UserIdentityInterface}.
     *
     * @return array the identity states that should be persisted.
     */
    public function getPersistentStates()
    {
        return $this->state;
    }

    /**
     * Sets an array of presistent states.
     *
     * @param array $states the identity states that should be persisted.
     */
    public function setPersistentStates($states)
    {
        $this->state = $states;
    }

    /**
     * Returns a value indicating whether the identity is authenticated.
     * This method is required by {@link UserIdentityInterface}.
     *
     * @return whether the authentication is successful.
     */
    public function getIsAuthenticated()
    {
        return $this->errorCode === self::ERROR_NONE;
    }

    /**
     * Gets the persisted state by the specified name.
     *
     * @param  string $name         the name of the state
     * @param  mixed  $defaultValue the default value to be returned if the named state does not exist
     * @return mixed  the value of the named state
     */
    public function getState($name, $defaultValue = null)
    {
        return isset($this->state[$name]) ? $this->state[$name] : $defaultValue;
    }

    /**
     * Sets the named state with a given value.
     *
     * @param string $name  the name of the state
     * @param mixed  $value the value of the named state
     */
    public function setState($name, $value)
    {
        $this->state[$name] = $value;
    }

    /**
     * Removes the specified state.
     * @param string $name the name of the state
     */
    public function clearState($name)
    {
        unset($this->state[$name]);
    }
}
