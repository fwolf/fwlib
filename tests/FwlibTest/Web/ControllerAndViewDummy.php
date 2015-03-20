<?php
namespace FwlibTest\Web;

use Fwlib\Web\RequestAwareTrait;

/**
 * Test dummy as a Controller and View
 *
 * Not implements interface because there are some method with same name with
 * different parameter.
 */
class ControllerAndViewDummy
{
    use RequestAwareTrait;


    /**
     * Param 1 is array in Controller, and string in View, so type hint removed.
     */
    public function getOutput()
    {
        return 'Output from dummy';
    }
}
