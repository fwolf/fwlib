<?php
namespace FwlibTest\Base;

use Fwlib\Base\SingleInstanceTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SingleInstanceTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | SingleInstanceTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            SingleInstanceTrait::class
        )
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function testGetInstance()
    {
        $mockInstance = $this->buildMock();

        $firstInstance = $mockInstance::getInstance();
        $this->assertEquals(
            get_class($mockInstance),
            get_class($firstInstance)
        );

        /** @noinspection PhpUndefinedFieldInspection */
        {
            // Add a temp property to test no duplicate 'new' operate
            $x = 'should exists in next getInstance()';
            $firstInstance->fooBar = $x;
            $this->assertEquals($x, $mockInstance::getInstance()->fooBar);
        }
    }
}
