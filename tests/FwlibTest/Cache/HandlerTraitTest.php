<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\HandlerTrait as CacheHandlerTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HandlerTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | CacheHandlerTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(CacheHandlerTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function testComputeExpireTime()
    {
        $handler = $this->buildMock();

        $expireTime = $this->reflectionCall($handler, 'computeExpireTime');
        $this->assertEquals(0, $expireTime);

        $expireTime =
            $this->reflectionCall($handler, 'computeExpireTime', [0]);
        $this->assertEquals(0, $expireTime);

        $expireTime =
            $this->reflectionCall($handler, 'computeExpireTime', [10, 300]);
        $this->assertEquals(310, $expireTime);

        $now = time();
        $expireTime =
            $this->reflectionCall($handler, 'computeExpireTime', [30, 0]);
        $this->assertGreaterThan($now + 20, $expireTime);
    }


    public function testHashKey()
    {
        $handler = $this->buildMock();

        $handler->emptyKeyReplacement = '[emptyKey]';

        $this->assertEquals(
            '[emptyKey]',
            $this->reflectionCall($handler, 'hashKey', [''])
        );

        $handler->hashAlgorithm = '';
        $hashedKey = $this->reflectionCall($handler, 'hashKey', ['foo']);
        $this->assertEquals('foo', $hashedKey);

        $handler->hashAlgorithm = 'crc32b';
        $hashedKey = $this->reflectionCall($handler, 'hashKey', ['foo']);
        $this->assertNotEquals('foo', $hashedKey);
        $this->assertNotEmpty($hashedKey);
    }
}
