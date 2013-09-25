<?php
namespace FwlibTest\Base;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Base\Rv;

/**
 * Dummy class for test
 */
class AbstractAutoNewConfigDummy extends AbstractAutoNewConfig
{
    public $rv;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        // Should call constructor of parent if exists
        parent::__construct($config);

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
