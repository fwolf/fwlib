<?php
namespace FwlibTest\Web;

/**
 * Test dummy as a Controller and View
 *
 * Not implements interface because there are some method with same name with
 * different parameter.
 */
class ControllerAndViewDummy
{
    /**
     * Param 1 is array in Controller, and string in View, so type hint removed.
     */
    public function getOutput()
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
}
