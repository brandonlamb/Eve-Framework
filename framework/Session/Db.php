<?php
namespace Eve\Session;

class Db implements DriverInterface, SweeperInterface
{
    /**
     * Config array
     *
     * @var array
     */
    protected $_config = array();

    public function __construct($config = array())
    {
        $this->_config = $this->validateConfig($config);
    }

    /**
     * Validate require config options
     *
     * @param  array $config
     * @return array
     */
    public function validateConfig(array $config)
    {
        // Verify required options
        if (!isset($config['lifetime'])) { $config['lifetime'] = 3600; }

        return $config;
    }

    /**
     * Load a session by ID.
     *
     * @param  string $sessionId
     * @return array
     */
    public function read($sessionId)
    {
        $session = $this->table()->find($sessionId);

        if (null === $session) {
            return array();
        }

        return array(
            'id' => $session->id,
            'last_activity' => $session->last_activity,
            'data' => $session->data,
        );
    }

    /**
     * Save a session.
     *
     * @param  array $session
     * @return void
     */
    public function write($session)
    {
        $this->delete($session['id']);

        $this->table()->insert(array(
            'id' => $session['id'],
            'last_activity' => $session['last_activity'],
            'data' => $session['data'],
        ));
    }

    /**
     * Delete a session by ID.
     *
     * @param  string $sessionId
     * @return void
     */
    public function destroy($sessionId)
    {
        $this->table()->delete($sessionId);
    }

    /**
     * Delete all expired sessions.
     *
     * @param  int  $expiration
     * @return void
     */
    public function garbageCollect($expiration)
    {
        $this->table()->where('last_activity', '<', $expiration)->delete();
    }

    /**
     * Get a session database query.
     *
     * @return string
     */
    private function table()
    {
        return $this->_config['table'];
    }
}
