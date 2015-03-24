<?php
namespace FwlibTest\Cache\Handler\Helper;

use Fwlib\Cache\Handler\Helper\WithVersionTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class WithVersionTraitTest extends PHPUnitTestCase
{
    /**
     * @var int[]
     */
    protected $versions = [];


    /**
     * @return MockObject | \Fwlib\Cache\Handler\Helper\WithVersionTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(WithVersionTrait::class)
            ->setMethods(['get', 'set'])
            ->getMockForTrait();

        $mock->expects($this->any())
            ->method('get')
            ->willReturnCallback(function($key) {
                return array_key_exists($key, $this->versions)
                    ? $this->versions[$key]
                    : null;
            });

        $mock->expects($this->any())
            ->method('set')
            ->willReturnCallback(function($key, $value) {
                $this->versions[$key] = $value;
            });

        /** @noinspection PhpUndefinedFieldInspection */
        $mock->versionSuffix = '-ver';

        return $mock;
    }


    public function test()
    {
        $handler = $this->buildMock();

        $key = 'foo';
        $this->assertEquals(1, $handler->getVersion($key));
        $this->assertEquals(2, $handler->increaseVersion($key));
        $this->assertEquals(2, $handler->getVersion($key));

        $this->versions['foo-ver'] = 65534;
        $this->assertEquals(65535, $handler->increaseVersion($key));
        $this->assertEquals(1, $handler->increaseVersion($key));
    }
}
