<?php

namespace utilit\core;

class bootstrap
{
    public static $baseDir = null;

    protected static $baseNamespace = 'utilit\\';
    protected static $ranOnce       = false;

    /**
     * Start up the bootstrapper
     *
     * @TODO - Ideally, there's no reason for this stuff to all live in the same place.. Build a pool, use it.
     *
     * @param string $directory Where to start
     * @return void
     */
    public static function start($directory=null)
    {
        if ($directory === null) {
            if (self::$ranOnce === true) {
                return;
            }
            self::$ranOnce = true;
            $directory = realpath(__DIR__.'/../../');
        }

        self::$baseDir = $directory;
        \spl_autoload_register(__NAMESPACE__.'\bootstrap::load');

        self::load(__NAMESPACE__.'\exceptions\base');
        Env::setup();
        Config::load();
    }

    /**
     * Try and load the class by breaking the namespace into a path.
     * We restrict this to our base namespace to try and play nicely with other autoloaders and not cause a general mess
     *
     * @param string $class The class name. We use namespaces, no need for underscore hell.
     * @return void
     */
    public static function load($class)
    {
        if (class_exists($class, false)) {
            return;
        }

        if (substr($class, 0, strlen(self::$baseNamespace)) == self::$baseNamespace) {
            $path = self::$baseDir.'/'.strtr($class, '\\', '/') . '.php';
            error_log('Loading: `'.$class.'` from '.$path);
            include_once $path;
        }
    }



}

bootstrap::start();
