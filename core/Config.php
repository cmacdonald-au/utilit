<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

class Config extends DataObject
{

    protected static $instance;
    protected static $cascades = true;

    const PATH   = 'config';
    const CONFIG = 'global';

    const TYPE_PHP  = 1;
    const TYPE_INI  = 2;
    const TYPE_XML  = 3;
    const TYPE_YAML = 4;
    const TYPE_JSON = 5;

    protected static $defaultConfig = 'global';

    public static function load($file=false)
    {
        $config = self::getInstance();

        if ($file === false) {
            $file = Env::get('config');
        }

        if ($file === false || empty($file)) {
            $file = self::CONFIG;
        }

        $path = Env::get('confdir');
        if ($path === false) {
            $path = self::PATH;
        }

        $configFile = $path.'/'.$file;
        Log::debug('Expecting config to be at `'.$configFile.'`');
        if (file_exists($configFile.'.php')) {
            include($configFile.'.php');
            return;
        }

        throw new Exception\structFailure('Config file `'.$configFile.'.php` not found');

    }

}
