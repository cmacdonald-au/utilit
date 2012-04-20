<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

abstract class DataObject implements \Countable, \ArrayAccess, \IteratorAggregate, \Serializable
{

    protected static $prefix;
    protected static $clonable;
    protected static $instance;
    protected static $cascades;

    protected $data;
    protected $iterKeys;
    protected $iterPosition = 0;

    public static function getInstance()
    {
        if (static::$instance) {
            return static::$instance;
        }

        static::$instance = new static(); 

        return static::$instance;
    }

    public static function extract($vars)
    {
        Log::debug(__METHOD__.'() Extracting vars from {'.var_export($vars, true).'}');
        if ($vars === false || empty($vars)) {
            throw new Exception\structFailure('Format of $vars is invalid {'.var_export($vars, true).'}');
        }

        $data = array();
        $prefixLen = strlen(static::$prefix);
        foreach ($vars as $k => $v) {
            if (strtolower(substr($k, 0, $prefixLen)) != static::$prefix) {
                Log::debug(__METHOD__.'() Skipping `'.$k.'`, does not start with `'.static::$prefix.'`');
                continue;
            }

            $remainder = strtolower(str_replace(static::$prefix.'_', '', strtolower($k)));
            Log::debug(__METHOD__.'() Taking `'.$remainder.'` from `'.$k.'`. Value: `'.$v.'`');
            $data[$remainder] = $v;
        }

        return $data;
    }

    public static function get($name)
    {
        $obj = self::getInstance();
        if (isset($obj->$name)) {
            return $obj->$name;
        }

        return false;
    }

    public static function set($name, $val)
    {
        self::getInstance()->$name = $val;
    }

    public static function clear($name)
    {
        unset(self::getInstance()->$name);
    }

    /**
     * Casting - To{What}
     */
    public function toArray()
    {
        $rtn = array();
        foreach ($this as $k => $v) {
            if ($v instanceOf self) {
                $rtn[$k] = $v->toArray();
            } else {
                $rtn[$k] = $v;
            }
        }
        return $rtn;
    }

    public function toObject()
    {
        $rtn = new \StdClass();
        foreach ($this as $k => $v) {
            if ($v instanceOf self) {
                $rtn->$k = $v->toObject();
            } else {
                $rtn->$k = $v;
            }
        }
        return $rtn;
    }

    public function toJson()
    {
        return json_encode($this->toObject());
    }

    /**
     * Standard overloaded accessors
     */
    public function __set($name, $value)
    {
        if (is_array($value)) {
            $this->data[$name] = new static();
            foreach ($value as $k => $v) {
                $this->data[$name]->$k = $v;
            }
        } else {
            $this->data[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property `'.$name.'` via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return null;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset ($name)
    {
        if (isset($this->data[$name]) === false) {
            return;
        }
        unset($this->data[$name]);
    }

    /**
     * Countable
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * ArrayAccess
     */
    public function offsetExists($offset)
    {
        return array_key_exists($this->data, $offset);
    }

    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $updateIterator = false;
        if (isset($this->data[$offset]) === false) {
            $updateIterator = true;
        }

        $this->data[$offset] = $value;
        if ($updateIterator) {
            $this->updateIterator($offset);
        }
    }

    public function offsetUnset($offset)
    {
        if (isset($this->data[$offset]) === false) {
            return;
        }

        unset($this->data[$offset]);
        $this->updateIterator($offset);
    }
    
    /**
     * IteratorAggregate
     */
    public function getIterator() {
        return new \ArrayIterator($this->data);
    }

    /**
     * Serializable
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    public function __clone()
    {
        if (static::$cloneable === false) {
            throw new Exception\structFailure('This object is marked as unclonable');
        }
    }

    public static function __set_state($data)
    {
        $obj = new static;
        if (isset($data['data']) === false) {
            throw new Exception\structFailure('Cannot set state from a non-compatible source');
        }
        foreach ($data['data'] as $k => $v) {
            $obj->$k = $v;
        }
        return $obj;
    }

}
