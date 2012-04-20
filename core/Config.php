<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

class Config extends DataObject
{

    protected static $instance;
    protected static $cascades = true;

    const PATH   = 'config';
    const CONFIG = 'global';

    const TYPE_AUTO = 0; // You're lazy and want us to guess the file type? Hrm.. kay.
    const TYPE_PHP  = 1;
    const TYPE_INI  = 2;
    const TYPE_XML  = 3;
    const TYPE_YAML = 4;
    const TYPE_JSON = 5;


    // If you use custom extensions, you'd better manage this yourself.
    public static $extMap = array(
        self::TYPE_PHP  => 'php', 
        self::TYPE_INI  => 'ini',
        self::TYPE_XML  => 'xml',
        self::TYPE_YAML => 'yml',
        self::TYPE_JSON => 'json',
    );


    protected static $defaultConfig = 'global';

    public static function load($file=false, $type=self::TYPE_AUTO)
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

        if ($type == self::TYPE_AUTO) {
            $ext = pathinfo($configFile, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $exts = self::$extMap;
            } else {
                $exts = array(array_search($ext, self::$extMap) => $ext);
            }
        } else {
            $exts = self::$extMap;
        }

        foreach ($exts as $type => $ext) {
            Log::debug('Looking for config file at `'.$configFile.'`');
            if (file_exists($configFile.'.'.$ext)) {
                try {
                    return self::parse($configFile.'.'.$ext, $type);
                } catch (Exception $e) {
                }
            }
        }

        throw new Exception\structFailure('Could not find a config file matching `'.$configFile.'`');

    }

    protected static function parse($file, $type)
    {
        if ($type == self::TYPE_PHP) {
            Log::debug('Loading `'.self::$extMap[$type].'` file from `'.$file.'`');
            include $file;
            return $config;
        } else if ($type == self::TYPE_JSON) {
            $data = json_decode(file_get_contents($file));
            foreach ($data as $k => $v) {
                static::set($k, $v);
            }
        }
    }


}
