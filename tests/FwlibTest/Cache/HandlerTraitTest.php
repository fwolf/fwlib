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


    public function testHashKey()
    {
        $handler = $this->buildMock();

        $handler->hashAlgorithm = 'crc32b';
        $handler->emptyKeyReplacement = '[emptyKey]';

        $this->assertEquals(
            '[emptyKey]',
            $this->reflectionCall($handler, 'hashKey', [''])
        );
        $hashedKey = $this->reflectionCall($handler, 'hashKey', ['foo']);
        $this->assertNotEquals('[emptyKey]', $hashedKey);
        $this->assertNotEmpty($hashedKey);
    }
}
