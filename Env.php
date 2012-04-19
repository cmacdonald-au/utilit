<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

class Env extends DataObject
{

    protected static $instance;
    protected static $prefix    = 'UTILIT';
    protected static $cloneable = false;

    protected $data = array(
        'prefix'  => '',
        'basedir' => '',
        'approot' => '',
        'docroot' => '',
        'confdir' => '',
        'config'  => '',
    );

    public static function setup($basedir=null)
    {
        if (isset($_SERVER['UTILIT_PREFIX'])) {
            self::$prefix = $_SERVER['UTILIT_PREFIX'];
        }

        self::setDefaults();

        $instance = self::getInstance();
        $data     = self::extract($_SERVER);
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                $instance->$k = $v;
            }
        }

    }

    protected static function setDefaults()
    {
        $instance = self::getInstance();
        $basedir = realpath(bootstrap::$baseDir.'/../');
        $instance->basedir = $basedir;
        $instance->confdir = $basedir.'/config';
    }


}
