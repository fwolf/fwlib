<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\HandlerFactory as CacheHandlerFactory;
use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HandlerFactoryTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | CacheHandlerFactory
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            CacheHandlerFactory::class,
            null
        );

        return $mock;
    }


    public function testCreate()
    {
        $factory = $this->buildMock();

        $this->assertInstanceOf(
            CacheHandlerInterface::class,
            $factory->create('file')
        );
    }


    /**
     * @expectedException \Fwlib\Cache\Exception\CacheHandlerNotImplementedException
     */
    public function testCreateWithUnknownType()
    {
        $factory = $this->buildMock();

        $factory->create('notImplementedType');
    }
}
