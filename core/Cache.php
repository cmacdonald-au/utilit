<?php

namespace utlit\core;

abstract class Cache
{

    const FLAG_LAZY     = 1;
    const FLAG_MINT     = 2;
    const FLAG_MINTMIN  = 4;
    const FLAG_MINTAMT  = 8;

    protected $prefix  = __NAMESPACE__;
    protected $ttl     = 3600;
    protected $mintTtl = 30;

    protected static $pool = array();

}
