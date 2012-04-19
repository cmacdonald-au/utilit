<?php

namespace utilit\core\Datastore;

use utilit\core\exceptions\base as Exception;

class MySQLi extends \mysqli implements workerInterface
{

    protected $dsn = 'mysql:';
    protected $config;

    public static $pool;
 
    public static function initialise($params)
    {

    }


    public function connect($host=null, $username=null, $passwd=null, $dbname=null, $port=null, $socket=null)
    {
        $dsn = self::getDSN();
        if (in_array($pool, $dsn)) {
            return self::getConn($dsn);
        }

        return self::createConn($dsn);
    }

    public static function getConn($dsn)
    {
       if ($pool[$dsn]->ping()) {
           return $pool[$dsn];
       }

       unset($pool[$dsn]);
       return self::createConn($dsn);
    }

    public static function createConn($dsn)
    {
        $ptr = new self($host, $username, $passwd, $dbname, $port, $socket);
        if ($ptr->connect_error) {
            throw new connectFailed('Failed with #'.$ptr->connect_errno.' ['.$ptr->connect_error.']');
        }
    }

    public function getDSN($params=null)
    {
        if (empty($params)) {
            return $this->dsn;
        }

        return self::buildDSN($params);
    }

    public static function buildDSN($params)
    {
        throw new Exception\notImplemented();
    }

}
