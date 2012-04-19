<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

abstract class BaseObject
{

    const COND_PRE  = 'pre';
    const COND_POST = 'post';

    const DATASTORE      = null;
    const DATASTORE_TYPE = null;
    const DATAKEY        = 'id';

    protected $loaded     = false;
    protected $cacheable  = false;
    protected $cacheFlags = null;
    protected $datastore  = null;

    protected $observers = null;

    protected $key   = null;
    protected $state = null;

    public static $dataFields = array(
    );

    public function __construct($key=null)
    {
        if (static::DATASTORE === null) {
            throw new Exception\structFailure('Must define a datastore');
        }

        if (static::DATASTORE_TYPE === null) {
            throw new Exception\structFailure('Must define a datastore type');
        }

        $this->datastore = Datastore::factory(static::DATASTORE_TYPE, static::DATASTORE);

        if ($key !== null) {
            $this->load($key);
        }
    }

    /**
     * Attach an observer
     *
     * Observers must implement BaseObjectObserver
     */
    public function attach($obj) {
        if (($obj instanceOf BaseObjectObserver) === false) {
            throw new Exception\InvalidObserver('Observers must implement BaseObjectObserver');
        }
        $this->observers[$obj->tag] = $obj;
    }

    /**
     * Detach an observer
     */
    public function detach($obj) {
        if (($obj instanceOf BaseObjectObserver) === false) {
            throw new Exception\InvalidObserver('Observers must implement BaseObjectObserver');
        }

        if (isset($this->observers[$obj->tag]) === false) {
            return;
        }

        unset($this->observers[$obj->tag]);
    }

    /**
     * Let observers know something happened
     */
    public function notify($state, $condition)
    {
        foreach ($this->observers as $k => $v) {
            try {
                $v->notification($state, $condition, $this);
            } catch (Exception $e) {
                core\Log::warn('Observer `'.$k.'` threw an exception. Code:`'.$e->getCode().'` Message:`'.$e->getMessage().'`');
            }
        }
    }

    /**
     * Core load function. Takes a DATAKEY and attempts to extract the data from the DATASTORE
     *
     * Will run pre and post conditionals.
     *
     * @param string $key The datastore key designator
     * @param bool $reloading True if we are in a `reload()` state
     * @return void
     */
    protected function load($key=null, $reloading=false)
    {
        if ($key === null) {
            if (!empty($key)) {
                $this->key = $key;
            } else {
                throw new Exception\structFailure('No key provided, none pre-set');
            }
        }
       $data = false;

       $this->runConditional(__FUNCTION__, self::COND_PRE);

        if ($this->loaded === true && $reloading === false) {
            $data = $this->refresh();
        }

        if ($data === false) {
            if ($this->cacheable) {
                $data = $this->getFromCache();
            }
            if ($data === false) {
                $data = $this->getFromDatastore();
            }
            if ($data === false) {
                throw new Exception\loadFailure('Failed to load data');
            }
        }

        if ($this->loadData($data) === false) {
            throw new Exception\loadFailure('Failed to load data {'.var_export($data, true).'}');
        }

        $this->runConditional(__FUNCTION__, self::COND_POST);
    }

    /**
     * Attempt to load the data for this object from it's designated datastore using the provided key
     *
     * @param $key The datastore key designator
     * @return mixed False on failure, anything else on success
     */
    protected function loadFromDatastore()
    {
        return $this->datastore->fetch($this->key, $this->dataFields);
    }

    /**
     * Attempt to load the data from cache
     *
     * @param $key The datastore key designator
     * @return mixed False on failure, anything else on success
     */
    protected function loadFromCache()
    {
        return $this->cache->fetch($this->key);
    }

    /**
     * Attempt to run the relevant conditional
     *
     * @param $source string The source function, (load, save, etc)
     * @param $condition string The conditionm (ie; pre, post)
     */
    protected function runConditional($source, $condition)
    {
        $conditional = $source;
        if ($condition === self::COND_PRE) {
            $conditional = self::COND_PRE;
        } else {
            $conditional = self::COND_POST;
        }
        $conditional .= ucfirst(strtolower($source));

        if (is_callable(array($this, $conditional)) === false) {
            return true;
        }

        try {
            if ($this->$conditional() === false) {
                throw new Exception\conditionalFailure($conditional.' failed');
            }
        } catch (Exception $e) {
            throw new Exception\conditionalFailure($conditional.' failed with `'.$e->getMessage().'`');
        }

    }

/*
    abstract protected function preLoad();
    abstract protected function postLoad();
    abstract protected function preSave();
    abstract protected function postSave();
*/

}
