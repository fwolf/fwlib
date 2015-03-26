<?php
namespace FwlibTest\Aide\ObjectMockBuilder;

use Fwlib\Base\ServiceContainerInterface;
use FwlibTest\Aide\TestServiceContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FwlibBaseServiceContainerTrait
{
    /**
     * @return  MockObject | ServiceContainerInterface
     */
    protected function buildServiceContainerMock()
    {
        /** @var PHPUnitTestCase|static $this */
        $mock = $this->getMockBuilder(TestServiceContainer::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
