<?php

namespace utilit\core;

use utilit\core\exceptions\base as Exception;

class Datastore
{

    public $config;
    public $type;
    public static $pool;

    protected $conn;

    const TYPE_NULL    = 0;
    const TYPE_CORE    = 1;
    const TYPE_FOUNDRY = 2;
    const TYPE_EXT     = 3;

    const BACKEND_MYSQL    = 'MySQLi';
    const BACKEND_PGSQL    = 2; //pg_
    const BACKEND_MEMCACHE = 3; //memcache_
    const BACKEND_PDO      = 4; //PDO_

    public static $backendMap = array(
        'mysql'    => self::BACKEND_MYSQL,
        'pgsql'    => self::BACKEND_PGSQL,
        'postgres' => self::BACKEND_PGSQL,
        'memcache' => self::BACKEND_MEMCACHE,
    );

    public static function factory($type, $params)
    {

        if ($type != self::TYPE_CORE) {
            throw new Exception\notImplemented('Only TYPE_CORE is currently supported');
        }

        if (is_string($params)) {
            $params = (array)json_decode($params);
            if ($params == false || empty($params)) {
                throw new Exception\structFailure('$params must be a jsonified dict');
            }
        }

        $type = $params['type'];
        unset($params['type']);

        if (array_key_exists($type, self::$backendMap) === false) {
            throw new Exception\structFailure('Invalid type `'.$type.'`. Must be one of {'.var_Export(self::$backendMap, true).'}');
        }

        return self::get(self::$backendMap[$type], $params);
    }

    public static function get($type, $params)
    {
        $ds = new self($type, $params);
        self::$pool[$ds->getDSN()] = $ds;

        return self::$pool[$ds->getDSN()];
    }

    public function __construct($type, $params=null)
    {
        if (in_array($type, self::$backendMap) === false) {
            throw new Exception\structFailure('Invalid type `'.$type.'` specified');
        }

        $this->type   = $type;
        $this->config = $params;

        $class = __CLASS__.'\\'.$type;
        $this->conn = call_user_func(array($class, 'initialise'), $this->config);
    }

    public function getDSN()
    {
        if (empty($this->conn)) {
            throw new Exception\structFailure('Impossible to get DSN for an empty connection');
        }
        return $this->conn->getDSN();
    }

}

namespace utilit\core\Datastore;

interface workerInterface 
{
    public static function initialise($config);
    public static function createConn($dsn);
    public function getDSN($params=null);
}

class connectFailed extends \Exception {}
class queryFailed extends \Exception {}
class transactionAborted extends \Exception {}
class connectionLost extends \Exception {}
