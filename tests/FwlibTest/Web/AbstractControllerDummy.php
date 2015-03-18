<?php
namespace FwlibTest\Web;

/**
 * Test dummy for Controller and View
 *
 * Not implements interface because there are some method with same name with
 * different parameter.
 */
class AbstractControllerDummy
{
    /**
     * Param 1 is array in Controller, and string in View, so remove type hint.
     */
    public function getOutput($request = null)
    {
        return 'Output from dummy';
    }


    public function setAction($action)
    {
        return $this;
    }


    public function setModule($module)
    {
        return $this;
    }


    public function setPathToRoot($pathToRoot)
    {
    }
}
