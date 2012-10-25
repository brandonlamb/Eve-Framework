<?php
namespace Eve;

// Namespace aliases
use Eve\Mvc as Mvc;

class Logger extends Mvc\Component
{
    /**
     * @var int Log level
     */
    protected $_log = 0;

    /**
     * @var array Log levels
     */
    protected $_levels = array(
        0 => 'FATAL',
        1 => 'ERROR',
        2 => 'WARN',
        3 => 'INFO',
        4 => 'DEBUG'
   );

    /**
     * @var string Absolute path to log directory with trailing slash
     */
    protected $_directory;

    /**
     * Constructor
     *
     * @param string $directory Absolute or relative path to log directory
     * @param int    $level     The maximum log level reported by this Logger
     */
    public function __construct($directory, $level = 4)
    {
        $this->setDirectory($directory);
        $this->setLevel($level);
    }

    /**
     * Set log directory
     *
     * @param  string $directory Absolute or relative path to log directory
     * @return void
     */
    public function setDirectory($directory)
    {
        $this->_directory = ($directory) ? realpath(rtrim($directory, '/') . '/') : false;
    }

    /**
     * Get log directory
     *
     * @return string|false Absolute path to log directory with trailing slash
     */
    public function getDirectory()
    {
        return $this->_directory;
    }

    /**
     * Set log level
     *
     * @param int The maximum log level reported by this Logger
     * @return void
     * @throws InvalidArgumentException If level specified is not 0, 1, 2, 3, 4
     */
    public function setLevel($level)
    {
        $level = (int) $level;
        if ($level >= 0 && $level <= 4) {
            $this->_level = $level;
        } else {
            throw new InvalidArgumentException('Invalid Log Level. Must be one of: 0, 1, 2, 3, 4.');
        }
    }

    /**
     * Get log level
     *
     * @return int
     */
    public function getLevel()
    {
        return (int) $this->_level;
    }

    /**
     * Log debug data
     *
     * @param  mixed $data
     * @return void
     */
    public function debug($data)
    {
        $this->_log($data, 4);
    }

    /**
     * Log info data
     *
     * @param  mixed $data
     * @return void
     */
    public function info($data)
    {
        $this->_log($data, 3);
    }

    /**
     * Log warn data
     *
     * @param  mixed $data
     * @return void
     */
    public function warn($data)
    {
        $this->_log($data, 2);
    }

    /**
     * Log error data
     *
     * @param  mixed $data
     * @return void
     */
    public function error($data)
    {
        $this->_log($data, 1);
    }

    /**
     * Log fatal data
     *
     * @param  mixed $data
     * @return void
     */
    public function fatal($data)
    {
        $this->_log($data, 0);
    }

    /**
     * Get absolute path to current daily log file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->getDirectory() . strftime('%Y-%m-%d') . '.log';
    }

    /**
     * Log data to file
     *
     * @param  mixed            $data
     * @param  int              $level
     * @return void
     * @throws RuntimeException If log directory not found or not writable
     */
    protected function _log($data, $level)
    {
        $dir = $this->getDirectory();
        if ($dir == false || !is_dir($dir)) {
            throw new RuntimeException("Log directory '$dir' invalid.");
        }

        if (!is_writable($dir)) {
            throw new RuntimeException("Log directory '$dir' not writable.");
        }

        if ($level <= $this->getLevel()) {
            $this->_write(sprintf("[%s] %s - %s\r\n", $this->_levels[$level], date('c'), (string) $data));
        }
    }

    /**
     * Persist data to log
     *
     * @param string Log message
     * @return void
     */
    protected function _write($data)
    {
        @file_put_contents($this->getFile(), $data, FILE_APPEND | LOCK_EX);
    }
}
