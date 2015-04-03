<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\RowAdjusterAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RowAdjusterAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | RowAdjusterAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RowAdjusterAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $rowAdjusterAware = $this->buildMock();

        $rowAdjusterAware->setRowAdjuster('substr');
        $this->assertTrue(is_callable(
            $this->reflectionCall($rowAdjusterAware, 'getRowAdjuster')
        ));
    }
}
