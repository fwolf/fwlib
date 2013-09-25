<?php
namespace FwlibTest\Base;

use Fwlib\Base\AbstractAutoNewObj;
use Fwlib\Base\Rv;

/**
 * Dummy class for test
 */
class AbstractAutoNewConfigDummy extends AbstractAutoNewObj
{
    public $rv;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Should call constructor of parent if exists
        //parent::__construct();

        // Unset for auto new
        unset($this->rv);
    }


    /**
     * New rv object
     *
     * @return Fwlib\Base\Rv
     */
    protected function newObjRv()
    {
        return new Rv;
    }
}
