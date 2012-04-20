<?php

self::set('datastores', array(
    'mysql' => array(
        'params' => array(
        ),
        'default' => array(
            'host'   => '127.0.0.1',
            'user'   => 'utilit',
            'pass'   => '',
            'db'     => 'utilit',
            'params' => array(
                'transactions' => true,
                'master' => true,
            )
        ),
        'read' => array(
            'host'   => 'localhost',
            'user'   => 'utilit',
            'pass'   => '',
            'db'     => 'utilit',
            'params' => array(
                'transactions' => false,
            )
        ),
    ),
    'pgsql' => array(
        'host'   => 'localhost',
        'user'   => 'utilit',
        'pass'   => '',
        'db'     => 'utilit',
        'params' => array(
            'transactions' => true,
            'master' => true,
        )
    ),
    'memcache' => array(
        'primary' => array(
            'host' => 'localhost',
            'port' => '11211',
        ),
        'sessions' => array(
            'host' => 'localhost',
            'port' => '11222'
        )
    )
)
);

