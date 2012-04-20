<?php

namespace utilit\core\Datastore;

use utilit\core\exceptions\base as Exception;
use utilit\core as Core;

class MySQLi extends \mysqli implements workerInterface
{

    protected $dsn = 'mysql:';
    protected $config;

    public static $pool;
 
    public static function initialise($params)
    {
        Core\Log::debug('Initialising with {'.var_export($params, true).'}');
        $dsn = self::buildDsn($params);
        return self::getConn($dsn);
    }

    public static function getConn($dsn)
    {
       if (isset($pool[$dsn]) && $pool[$dsn]->ping()) {
           return $pool[$dsn];
       }

       unset($pool[$dsn]);
       $pool[$dsn] = self::createConn($dsn);
       return $pool[$dsn];
    }

    protected static function createConn($dsn)
    {
        $ptr = new self($dsn);
        if ($ptr->connect_error) {
            throw new connectFailed('Failed with #'.$ptr->connect_errno.' ['.$ptr->connect_error.']');
        }
        return $ptr;
    }

    public function __construct($dsn)
    {
        $this->dsn = $dsn;
        $params = json_decode($dsn);
        parent::__construct($params->host, $params->user, $params->pass, $params->db, $params->port, $params->socket);
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
        return json_encode($params);

        throw new Exception\notImplemented();
    }

}
