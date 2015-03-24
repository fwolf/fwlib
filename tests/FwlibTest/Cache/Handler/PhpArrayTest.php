<?php
namespace FwlibTest\Cache\Handler;

use Fwlib\Cache\Handler\PhpArray as PhpArrayHandler;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PhpArrayTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | PhpArrayHandler
     */
    protected function buildMock()
    {
        $mock = $this->getMock(PhpArrayHandler::class, null);

        return $mock;
    }


    public function test()
    {
        $handler = $this->buildMock();

        $key = 'foo';
        $val = 'bar';
        $handler->set($key, $val);
        $this->assertEquals($val, $handler->get($key));

        $handler->delete($key);
        $this->assertEquals(null, $handler->get($key));
    }


    public function testIsExpired()
    {
        $handler = $this->buildMock();

        $this->assertTrue($handler->isExpired('any key'));

        $handler->set('foo', 'bar', -10);
        $this->assertTrue($handler->isExpired('foo'));
        $this->assertEmpty($handler->get('foo'));

        $handler->set('foo', 'bar', 10);
        $this->assertFalse($handler->isExpired('foo'));
        $this->assertEquals('bar', $handler->get('foo'));
    }
}
