<?php
namespace Fwlib\Base\Test;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Base\ReturnValue;

/**
 * Dummy class for test
 */
class AbstractAutoNewConfigDummy extends AbstractAutoNewConfig
{
    public $abstractAutoNewConfigDummy = null;
    public $returnValue = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        // Unset for auto new
        unset($this->abstractAutoNewConfigDummy);
        unset($this->returnValue);

        // Should call constructor of parent if exists
        parent::__construct($config);
    }


    /**
     * New returnValue property
     *
     * @return Fwlib\Base\ReturnValue
     */
    protected function newInstanceReturnValue()
    {
        return new ReturnValue;
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
