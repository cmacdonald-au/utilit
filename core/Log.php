<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

class Log
{

    const OUTPUT_STDERR = 0;
    const OUTPUT_STDOUT = 1;
    const OUTPUT_FILE   = 2;
    const OUTPUT_SYSLOG = 3;

    const LOG_EMERG   = 0; // A panic condition.  This is normally broadcast to all users.
    const LOG_ALERT   = 1; // A condition that should be corrected immediately, such as a corrupted system database.
    const LOG_CRIT    = 2; // Critical conditions, e.g., hard device errors.
    const LOG_ERR     = 3; // Errors.
    const LOG_WARNING = 4; // Warning messages.
    const LOG_NOTICE  = 5; // Conditions that are not error conditions, but should possi-bly be handled specially.
    const LOG_INFO    = 6; // Informational messages.
    const LOG_DEBUG   = 7; // Messages that contain information normally of use only when debugging a program.

    /**
     * Straight map from named levels to their syslog counterparts
     *
     * To expose a level in a custom way, set the function name as the key and the value to the appropriate LOG_XXX const
     */
    public static $levelMap = array(
        'emerg'  => self::LOG_EMERG,
        'alert'  => self::LOG_ALERT,
        'crit'   => self::LOG_CRIT,
        'error'  => self::LOG_ERR,
        'warn'   => self::LOG_WARNING,
        'notice' => self::LOG_NOTICE,
        'info'   => self::LOG_INFO,
        'debug'  => self::LOG_DEBUG
    );

    /**
     * Default to debug 7 stderr
     */
    public $level  = 7;
    public $output = 0;

    /**
     * If we're working with a stream or fp, we store the handle here
     */
    protected $outputResource = null;

    protected static $instance;

    public function __construct($args=null)
    {
        if ($this->output === self::OUTPUT_SYSLOG) {
            openlog(__NAMESPACE__, LOG_CONS | LOG_NDELAY | LOG_PID); 
        } else if ($this->output === self::OUTPUT_FILE) {
            $this->outputResource = fopen($args, 'a');
        } else if ($this->output === self::OUTPUT_STDOUT || $this->output === self::OUTPUT_STDERR) {
            return;
        } else {
            throw new \Exception\invalidOutput('`'.$this->output.'` is not a valid output channel');
        }
    }

    public function __destruct()
    {
        if ($this->output === self::OUTPUT_SYSLOG) {
            closelog();
        } else if ($this->output === self::OUTPUT_FILE) {
            fclose($this->outputResource);
        }
    }

    /**
     * Write the message to the output resource in whatever manner is appropriate.
     *
     * If the level specified is higher than $this->level, do not do anything
     *
     * @param int $level One of the self::LOG_XXX consts
     * @param string $msg The message to write
     */
    public function write($level, $msg)
    {
        if ($level > $this->level) {
            return;
        }

        if ($this->output === self::OUTPUT_SYSLOG) {
            syslog($this->level, $msg);
        } else if ($this->output === self::OUTPUT_STDERR) {
            error_log($msg);
        } else if ($this->output === self::OUTPUT_STDOUT) {
            print $msg;
        } else {
            fwrite($this->outputResource, $msg);
        }
    }
 
    public static function getInstance()
    {
        if (false === (self::$instance instanceOf self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        self::sendMsg($name, $arguments);
    }

    public function __call($name, $arguments)
    {
        self::sendMsg($name, $arguments, $this);
    }

    /**
     * "Send" the message to the log.
     *
     * You can provide a specific log instance to use for this message.
     * By default, we append the class of the instance and the level as a prefix.
     *
     * @param string $level A key from self::$levelMap
     * @param array $args A list of messages to send
     * @param utilit\Core\Log $instance Optional instance to use for this message
     */
    public static function sendMsg($level, $args, $instance=null)
    {
        if ($instance === null) {
            $instance = self::getInstance();
        }

        if (isset(self::$levelMap[$level]) === false) {
            throw new \Exception\invalidLevel('`'.$level.'` not recognised. Must be one of {'.implode(', ', array_keys(self::$levelMap)).'}');
        }
        $prefix = '['.get_class($instance).'.'.$level.'] ';

        foreach ($args as $arg) {
            $instance->write(self::$levelMap[$level], $prefix.$arg);
        }
    }

}
