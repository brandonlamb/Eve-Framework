<?php
namespace Eve;

// Namespace aliases
use Eve\Mvc as Mvc;
use Eve\Session as Session;

class Session extends Mvc\Component
#class Session extends Mvc\Component implements \SessionHandlerInterface
{
    /**
     * The session id
     *
     * @var string
     */
    protected $_sessionId;

    /**
     * The active session driver.
     *
     * @var Session\Driver
     */
    protected $_driver;

    /**
     * The session payload, which contains the session ID, data and last activity timestamp.
     *
     * @var array
     */
    protected $_session = array();

    /**
     * Random string pool
     *
     * @var string
     */
    const RANDOM_POOL = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Default lifetime if no config option is set
     *
     * @var int
     */
    const LIFETIME = 60;

    /**
     * Load session driver
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        // Verify required options
        if (!isset($config['lifetime'])) {
            $this->_config['lifetime'] = static::LIFETIME;
        }

#		session_set_save_handler($this, true);
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'garbageCollect')
        );
        register_shutdown_function(array($this, 'close'));

        // Determine which driver to use
        switch (@$config['driver']) {
            case 'cookie':
                $class = 'Eve\\Session\\Cookie';
                $options = $config['options']['cookie'];
                break;

            case 'file':
                $class = 'Eve\\Session\\File';
                $options = $config['options']['file'];
                break;

            case 'db':
                $class = 'Eve\\Session\\Db';
                $options = $config['options']['db'];
                break;

            case 'memcached':
                $class = 'Eve\\Session\Memcached';
                $options = $config['options']['memcached'];
                break;

            case 'apc':
                $class = 'Eve\\Session\Apc';
                $options = $config['options']['apc'];
                break;

            default:
                $class = 'Eve\\Session\File';
                $options = $config['options']['file'];
        }

        // Set driver object and pass config
        $this->setDriver(new $class($options));

        // Start sessions
        session_start();
    }

    /**
     * Magic getter for $_SESSION[$key]
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic setter for $_SESSION[$key]
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Create a new session object based on driver
     *
     * @param  string         $driver
     * @return Session\Driver
     */
    public function getDriver()
    {
        return $this->_driver;
    }

    /**
     * Set the session driver
     *
     * @param  Session\DriverInterface $driver
     * @return Session
     */
    public function setDriver(Session\DriverInterface $driver)
    {
        $this->_driver = $driver;

        return $this;
    }

    /**
     * Open session
     *
     * @param  string $path
     * @param  string $sessionId
     * @return bool
     */
    public function open($path, $sessionId)
    {
        return $path && $sessionId;
    }

    /**
     * Close session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Load a user session from driver by session_id.
     * _session is an array with following signature
     * _session['data'] = $_SESSION
     * _session['id'] = session_id()
     * _session['last_activity'] = time()
     * _session['csrf_token'] = security token
     *
     * @param  string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        // Read session data from driver
        $this->_session = $this->getDriver()->read($sessionId);

        // If the session is invalidated, setup a new array with blank data
        if ($this->invalid($this->_session)) {
            $this->_session = array(
                'id' => $sessionId,
                'data' => '',
                'csrf_token' => static::random(16),
            );
        }

        // Generate a new CSRF token if one is not set
        if (!isset($this->_session['csrf_token'])) {
            $this->_session['csrf_token'] = static::random(16);
        }

        // Update last activity time
        $this->_session['last_activity'] = time();

        // Return serialized session array
        return (string) $this->_session['data'];
    }

    /**
     * Close the session.
     *
     * The session will be stored in persistant storage and the session cookie will be
     * session cookie will be sent to the browser. The old input data will also be
     * stored in the session flash data.
     *
     * @return bool
     */
    public function write($sessionId, $data)
    {
        $this->_session['id'] = $sessionId;
        $this->setData($data);
        $this->getDriver()->write($this->_session);

#		$this->ageFlash();
#		$this->flash('session_old_get', &$_GET);
#		$this->flash('session_old_post', &$_POST);
#		$this->writeCookie();

        return true;
    }

    /**
     * Remove all items from the session.
     *
     * @return bool
     */
    public function destroy()
    {
        $this->_session = array('id' => null, 'data' => '');
        $this->getDriver()->destroy();

        return true;
    }

    /**
     * Perform garbage collection
     *
     * @return bool
     */
    public function garbageCollect()
    {
        if (!$this->getDriver() instanceof Session\Sweeper) {
            return true;
        }
        $this->getDriver()->garbageCollect(time() - $this->_config['lifetime']);

        return true;
    }

    /**
     * Get random bytes
     *
     * @param  int    $length
     * @return string
     */
    public static function random($length = 32)
    {
        $value = '';
        $pool = static::RANDOM_POOL;
        $poolLength = strlen($pool) - 1;

        for ($i = 0; $i < $length; $i++) {
            $value .= $pool[mt_rand(0, $poolLength)];
        }

        return $value;
    }

    /**
     * Determine if a session is valid. A session is considered valid if it exists and has not expired
     *
     * @param  array $session
     * @return bool
     */
    protected function invalid($session)
    {
        $lastActivity = isset($session['last_activity']) ? (int) $session['last_activity'] : time();

        return null === $session || time() - $lastActivity > $this->_config['lifetime'];
    }

    /**
     * Determine if the session or flash data contains an item.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return (null !== $this->get($key));
    }

    /**
     * Get an item from the session or flash data.
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        foreach (array($key, ':old:' . $key, ':new:' . $key) as $possibility) {
            if (isset($_SESSION[$possibility])) {
                return $_SESSION[$possibility];
            }
        }

        return $default;
    }

    /**
     * Write an item to the session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return Session
     */
    public function set($key, $value = null)
    {
        if (null === $value) {
            unset($_SESSION[$key]);
        } else {
            $_SESSION[$key] = $value;
        }

        return $this;
    }

    /**
     * Regenerate the session ID.
     *
     * @return Session
     */
    public function regenerate()
    {
        $this->getDriver()->delete(session_id());
        session_regenerate_id(true);

        return $this;
    }

    /**
     * Write the session cookie.
     *
     * @return void
     */
    protected function writeCookie()
    {
        if (!headers_sent()) {
            $sessionName = $this->_config['sessionName'];
            $minutes = ($this->_config['expire_on_close']) ? 0 : $this->_config['lifetime'];
            Eve\Cookie::put(
                $sessionName,
                $this->_session['id'],
                $minutes,
                $this->_config['path'],
                $this->_config['domain'],
                $this->_config['https'],
                $this->_config['http_only']
            );
        }
    }

    /**
     * Return raw session data array
     *
     * @return string
     */
    public function getData()
    {
        return (string) $this->_session['data'];
    }

    /**
     * Set entire session data array
     *
     * @param array $value
     */
    public function setData($value)
    {
        $this->_session['data'] = $value;

        return $this;
    }
}
