<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\RowRendererAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RowRendererAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | RowRendererAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RowRendererAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $rowRendererAware = $this->buildMock();

        $rowRendererAware->setRowRenderer('substr');
        $this->assertTrue(is_callable(
            $this->reflectionCall($rowRendererAware, 'getRowRenderer')
        ));
    }
}
