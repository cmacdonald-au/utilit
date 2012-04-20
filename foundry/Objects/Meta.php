<?php

namespace utilit\foundry\Objects;

use utilit\core as core;

class Meta extends core\BaseObject
{

    const DATASTORE      = '{"type":"mysql","table":"meta"}';
    const DATASTORE_TYPE = core\Datastore::TYPE_CORE;
    
    protected $cacheable = true;

    public static $dataFields = array(
        'id'    => 'int',
        'type'  => 'int',
        'value' => 'string'
    );

}
