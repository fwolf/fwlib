<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\HandlerAwareTrait as CacheHandlerAwareTrait;
use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HandlerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | CacheHandlerAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(CacheHandlerAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function testSetAndGet()
    {
        $handlerAware = $this->buildMock();
        $cacheHandler = $this->getMock(CacheHandlerInterface::class);

        $handlerAware->setCacheHandler($cacheHandler);
        $this->assertInstanceOf(
            CacheHandlerInterface::class,
            $this->reflectionCall($handlerAware, 'getCacheHandler')
        );
    }
}
