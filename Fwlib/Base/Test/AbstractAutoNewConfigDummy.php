<?php
namespace Fwlib\Base\Test;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Base\Rv;

/**
 * Dummy class for test
 */
class AbstractAutoNewConfigDummy extends AbstractAutoNewConfig
{
    public $abstractAutoNewConfigDummy = null;
    public $rv = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        // Unset for auto new
        unset($this->abstractAutoNewConfigDummy);
        unset($this->rv);

        // Should call constructor of parent if exists
        parent::__construct($config);
    }


    /**
     * New rv property
     *
     * @return Fwlib\Base\Rv
     */
    protected function newInstanceRv()
    {
        return new Rv;
    }


    /**
     * New self instance
     *
     * For test AbstractAutoNewInstance compative with newObjXxx().
     *
     * @return  Fwlib\Base\Test\AbstractAutoNewConfigDummy
     */
    protected function newObjAbstractAutoNewConfigDummy()
    {
        return $this;
    }
}
