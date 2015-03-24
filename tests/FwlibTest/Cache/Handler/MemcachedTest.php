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
     * get(), set(), del(), expire() etc
     */
    public function testCache()
    {
        $cache = $this->buildMock();

        // This should be a valid server
        $ms = GlobalConfig::getInstance()->get('memcached.server');


        // Memcache server recognize by array position, not assoc key
        $x = [
            'h' => $ms[0]['host'],
            'p' => $ms[0]['port'],
            'w' => $ms[0]['weight'],
        ];
        $y = [
            'host' => $ms[0]['host'],
            'port' => $ms[0]['port'],
            'weight' => $ms[0]['weight'],
        ];
        $cache->setMemcachedServers($x);
        $cache->get('any key');
        $serverList = $this->reflectionGet($cache, 'memcachedInstance')
            ->getServerList();
        // Memcached::getServerList() result may not include weight
        $this->assertEquals($y['host'], $serverList[0]['host']);
        $this->assertEquals($y['port'], $serverList[0]['port']);


        // Set server cfg, writable, $memcached is reset to null now
        $cache->setMemcachedServers($ms);
        // Do an operate to trigger memcached instance creation
        $cache->get('any key');
        $serverList = $this->reflectionGet($cache, 'memcachedInstance')
            ->getServerList();
        $this->assertEquals($ms[0]['host'], $serverList[0]['host']);
        $this->assertEquals($ms[0]['port'], $serverList[0]['port']);


        // Cache write
        $key = str_repeat('test', 8);
        $x = 'blah';
        $cache->set($key, $x, 60);
        $this->assertEquals($x, $cache->get($key));

        // Cache expire
        $cache->setConfig('memcachedAutoSplit', 1);
        $cache->set($key, $x, 60);
        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );
        $cache->delete($key);
        $this->assertTrue(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );
        $cache->set($key, $x, -10);
        $this->assertTrue(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );
        $cache->setConfig('memcachedAutoSplit', 0);
        $cache->set($key, $x, 60);
        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );
        $cache->set($key, $x, -10);
        $this->assertTrue(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );

        // Cache delete
        $cache->delete($key);
        $this->assertEquals(null, $cache->get($key));

        // Long key
        $key = str_repeat('-', 300);
        $x = 'foo';
        $cache->set($key, $x, 60);
        $this->assertEquals($x, $cache->get($key));
        $cache->delete($key);
        $this->assertEquals(null, $cache->get($key));

        // Empty key
        $key = '';
        $x = 'foo';
        $cache->set($key, $x, 60);
        $this->assertEquals($x, $cache->get($key));

        // Cache get with expire
        $key = str_repeat('test', 8);
        $cache->set($key, $x, -10);
        $this->assertEquals(null, $cache->get($key));
        $cache->set($key, $x, 0);
        $this->assertEquals($x, $cache->get($key));
        $cache->set($key, $x, 5);
        $this->assertEquals($x, $cache->get($key));
        $cache->set($key, $x, null);
        $this->assertEquals($x, $cache->get($key));


        // Big value exceed max item size
        $cache->setConfig('memcachedMaxItemSize', 100);

        $s = str_repeat('0', 300);
        $cache->delete($key);       // Clear previous set value
        $cache->setConfig('memcachedAutoSplit', 1);
        $cache->set($key, $s, 3600);
        $this->assertEquals($s, $cache->get($key));
        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );
        $cache->delete($key);
        $this->assertEquals(null, $cache->get($key));
        $this->assertTrue(
            $this->reflectionCall($cache, 'isExpired', [$key])
        );
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

        $handler = $this->buildMock();

        // This should be a valid server
        $servers = GlobalConfig::getInstance()->get('memcached.server');


        $handler->setMemcachedServers($servers);
        $result = $this->reflectionCall($handler, 'getValidMemcachedServers');
        $this->assertEquals(1, count($result));


        $badServers = $servers;
        $badServers[0]['port'] = 80;
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
     * @expectedException \Fwlib\Cache\Exception\CacheWriteFailException
     * @expectedExceptionMessage ITEM TOO BIG
     */
    public function testSetLargeItemFail()
    {
        $handler = $this->buildMock();
        $handler->setConfig('memcachedAutoSplit', 0);

        // This should be valid memcached server
        $servers = GlobalConfig::getInstance()->get('memcached.server');
        $handler->setMemcachedServers($servers);
        $memcached = $this->reflectionCall($handler, 'getMemcachedInstance');

        // Exceed max item size, without compress on
        $bigItem = str_repeat('0', 1200000);
        $memcached->setOption(\Memcached::OPT_COMPRESSION, false);

        // Error: Memcache set error 10: SERVER ERROR
        $handler->set('foo', $bigItem, 3600);
    }


    public function testSetLargeItemSuccessful()
    {
        $handler = $this->buildMock();
        $handler->setConfig('memcachedAutoSplit', 0);

        // This should be valid memcached server
        $servers = GlobalConfig::getInstance()->get('memcached.server');
        $handler->setMemcachedServers($servers);
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
