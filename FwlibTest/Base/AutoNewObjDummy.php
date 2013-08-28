<?php
namespace FwlibTest\Base;

use Fwlib\Base\AutoNewObj;

/**
 * Dummy class for test
 */
class AutoNewObjDummy extends AutoNewObj
{
    public $foo;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Should call constructor of parent if exists
        //parent::__construct();

        // Unset for auto new
        unset($this->foo);
    }


    /**
     * New foo object
     *
     * @return object
     */
    protected function newObjFoo()
    {
        return new AutoNewObj;
    }
}
