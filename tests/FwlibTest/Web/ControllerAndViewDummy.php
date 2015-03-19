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


    /**
     * @param   string  $action
     * @return  static
     */
    public function setAction($action)
    {
        // Dummy for inspection
        true || $action;

        return $this;
    }


    /**
     * @param   string  $module
     * @return  static
     */
    public function setModule($module)
    {
        // Dummy for inspection
        true || $module;

        return $this;
    }
}
