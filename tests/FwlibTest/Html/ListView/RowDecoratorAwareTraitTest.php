<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\RowDecoratorAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RowDecoratorAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject|RowDecoratorAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RowDecoratorAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $rowDecoratorAware = $this->buildMock();

        $rowDecoratorAware->setRowDecorator('substr');
        $this->assertTrue(is_callable(
            $this->reflectionCall($rowDecoratorAware, 'getRowDecorator')
        ));
    }
}
