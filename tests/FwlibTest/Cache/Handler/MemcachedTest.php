<?php
namespace FwlibTest\Cache\Handler;

use Fwlib\Cache\Handler\Memcached as MemcachedHandler;
use Fwlib\Config\GlobalConfig;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @requires extension memcached
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class MemcachedTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @return MockObject | MemcachedHandler
     */
    protected function buildMock()
    {
        $mock = $this->getMock(MemcachedHandler::class, null);

        return $mock;
    }


    /**
     * @return MockObject | MemcachedHandler
     */
    protected function buildMockWithMemcachedConnected()
    {
        $mock = $this->getMock(MemcachedHandler::class, null);

        // This should be valid memcached server
        $servers = GlobalConfig::getInstance()->get('memcached.server');

        /** @var MemcachedHandler $mock */
        $mock->setMemcachedServers($servers);

        return $mock;
    }


    public function testGetDefaultConfigsWhenJsonEnabled()
    {
        $handler = $this->getMock(
            MemcachedHandler::class,
            ['isMemcachedJsonEnabled']
        );

        $handler->expects($this->once())
            ->method('isMemcachedJsonEnabled')
            ->willReturn(true);

        $configs = $this->reflectionCall($handler, 'getDefaultConfigs');
        $this->assertEquals(
            \Memcached::SERIALIZER_JSON,
            $configs['memcachedOptions'][\Memcached::OPT_SERIALIZER]
        );
    }


    public function testGetValidMemcachedServers()
    {
        $factory = $this->getFunctionMockFactory(MemcachedHandler::class);
        $errorLogMock = $factory->get(null, 'error_log', true);

        $handler = $this->buildMockWithMemcachedConnected();


        $result = $this->reflectionCall($handler, 'getValidMemcachedServers');
        $this->assertEquals(1, count($result));


        $badServers = ['host' => '127.0.0.1', 'port' => 80];
        $errorLogMock->setResult('');
        $handler->setMemcachedServers($badServers);
        $result = $this->reflectionCall($handler, 'getValidMemcachedServers');
        $this->assertEmpty($result);
        $this->assertRegExp('/test fail/', $errorLogMock->getResult());


        $errorLogMock->disableAll();
    }


    public function testHashKey()
    {
        $handler = $this->buildMock();

        $this->assertEquals(
            'foo',
            $key = $this->reflectionCall($handler, 'hashKey', ['foo'])
        );

        // Exceed length limit
        $key = str_repeat('0', MemcachedHandler::MAX_KEY_LENGTH + 19) . 'A';
        $key = $this->reflectionCall($handler, 'hashKey', [$key]);
        $this->assertLessThanOrEqual(
            MemcachedHandler::MAX_KEY_LENGTH,
            strlen($key)
        );
        $this->assertEquals('0', substr($key, 0, 1));
        $this->assertEquals('A', substr($key, -1));
    }


    /**
     * get(), set(), del(), expire() etc
     */
    public function testNormalOperate()
    {
        $handler = $this->buildMockWithMemcachedConnected();

        // Cache write
        $key = str_repeat('test', 8);
        $x = 'blah';
        $handler->set($key, $x, 60);
        $this->assertEquals($x, $handler->get($key));


        // Cache expire
        $handler->setConfig('memcachedAutoSplit', 1);
        $handler->set($key, $x, 60);
        $this->assertFalse($handler->isExpired($key));

        $handler->delete($key);
        $this->assertTrue($handler->isExpired($key));

        $handler->set($key, $x, -10);
        $this->assertTrue($handler->isExpired($key));

        $handler->setConfig('memcachedAutoSplit', 0);
        $handler->set($key, $x, 60);
        $this->assertFalse($handler->isExpired($key));

        $handler->set($key, $x, -10);
        $this->assertTrue($handler->isExpired($key));


        // Cache delete
        $handler->delete($key);
        $this->assertEquals(null, $handler->get($key));


        // Cache get with expire
        $key = str_repeat('test', 8);
        $handler->set($key, $x, -10);
        $this->assertEquals(null, $handler->get($key));
        $handler->set($key, $x, 0);
        $this->assertEquals($x, $handler->get($key));
        $handler->set($key, $x, 5);
        $this->assertEquals($x, $handler->get($key));
        $handler->set($key, $x, null);
        $this->assertEquals($x, $handler->get($key));


        // Big value exceed max item size, will be splitted
        $handler->setConfig('memcachedMaxItemSize', 100);

        $s = str_repeat('0', 300);
        $handler->delete($key);       // Clear previous set value
        $handler->setConfig('memcachedAutoSplit', 1);
        $handler->set($key, $s, 3600);
        $this->assertEquals($s, $handler->get($key));
        $this->assertFalse($handler->isExpired($key));

        $handler->delete($key);
        $this->assertEquals(null, $handler->get($key));
        $this->assertTrue($handler->isExpired($key));
    }


    /**
     * @expectedException \Fwlib\Cache\Exception\CacheWriteFailException
     * @expectedExceptionMessage ITEM TOO BIG
     */
    public function testSetLargeItemFail()
    {
        $handler = $this->buildMockWithMemcachedConnected();
        $handler->setConfig('memcachedAutoSplit', 0);

        $memcached = $this->reflectionCall($handler, 'getMemcachedInstance');

        // Exceed max item size, without compress on
        $bigItem = str_repeat('0', 1200000);
        $memcached->setOption(\Memcached::OPT_COMPRESSION, false);

        // Error: Memcache set error 10: SERVER ERROR
        $handler->set('foo', $bigItem, 3600);
    }


    public function testSetLargeItemSuccessful()
    {
        $handler = $this->buildMockWithMemcachedConnected();
        $handler->setConfig('memcachedAutoSplit', 0);

        $memcached = $this->reflectionCall($handler, 'getMemcachedInstance');

        // Not exceed max item size, with compress on
        $bigItem = str_repeat('0', 1200000);
        $memcached->setOption(\Memcached::OPT_COMPRESSION, true);

        $handler->set('foo', $bigItem, 3600);
        $this->assertEquals($bigItem, $handler->get('foo'));


    }


    public function testSetMemcachedServer()
    {
        $handler = $this->buildMock();
        $configs = $this->reflectionCall($handler, 'getConfigInstance');

        $handler->setMemcachedServers();
        $this->assertEquals(0, count($configs->get('memcachedServers')));

        // This should be a valid server
        $ms = GlobalConfig::getInstance()->get('memcached.server');

        // Alive one
        $x = [
            'host'      => $ms[0]['host'],
            'port'      => $ms[0]['port'],
            'weight'    => 33
        ];
        $handler->setMemcachedServers($x);
        $this->assertEquals(1, count($configs->get('memcachedServers')));

        $y = [
            // Dead one
            [
                'host'      => $ms[0]['host'],
                'port'      => 80,
                'weight'    => 67,
            ],
            $x,
        ];
        $handler->setMemcachedServers($y);
        // Server alive test is not applied yet
        $this->assertEquals(2, count($configs->get('memcachedServers')));

        $memcached = $this->reflectionGet($handler, 'memcached');
        $this->assertNull($memcached);
    }


    /**
     * @expectedException   \Fwlib\Cache\Exception\CacheWriteFailException
     */
    public function testSetWithFail()
    {
        $handler = $this->buildMock();

        $handler->set('dummy', 'will fail duo to no server set');
    }
}
