<?php
namespace Fwlib\Mvc\Test;


/**
 * Test dummy for Controler and View
 *
 * Not implements interface because there are some method with same name with
 * different parameter.
 */
class AbstractControlerDummy
{
    /**
     * Param 1 is array in Controler, and string in View, so remove type hint.
     */
    public function getOutput($request = null)
    {
        return 'Output from dummy';
    }


    public function setPathToRoot($pathToRoot)
    {
    }


    public function setServiceContainer($serviceContainer)
    {
    }
}