<?php

include_once '../bootstrap.php';

class ObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        structFailure
     * @expectedExceptionMessage No key provided, none pre-set
     */
    public function test()
    {
        $o = $this->getMock('utilit\core\Object');
        $o->load();
    }


}
