<?php
namespace FwlibTest\Cache\Handler;

use Fwlib\Cache\Cache;
use Fwlib\Cache\Handler\Memcached as MemcachedHandler;
use Fwlib\Config\GlobalConfig;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @requires extension memcached
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class MemcachedTest extends PHPUnitTestCase
{
    /**
     * @return MemcachedHandler
     */
    protected function buildMock()
    {
        return Cache::create('memcached');
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
        $cache->setConfigServer($x);
        $cache->get('any key');
        $serverList = $this->reflectionGet($cache, 'memcached')
            ->getServerList();
        // Memcached::getServerList() result may not include weight
        $this->assertEquals($y['host'], $serverList[0]['host']);
        $this->assertEquals($y['port'], $serverList[0]['port']);


        // Set server cfg, writable, $memcached is reset to null now
        $cache->setConfigServer($ms);
        // Do an operate to trigger memcached instance creation
        $cache->get('any key');
        $serverList = $this->reflectionGet($cache, 'memcached')
            ->getServerList();
        $this->assertEquals($ms[0]['host'], $serverList[0]['host']);
        $this->assertEquals($ms[0]['port'], $serverList[0]['port']);


        // Cache write
        $key = str_repeat('test', 8);
        $x = 'blah';
        $cache->set($key, $x, 60);
        $this->assertEquals($x, $cache->get($key));

        $x = ['blah', ['foo' => 'boo']];
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
        $x = 'blah';
        $cache->set($key, $x, 60);
        $this->assertEquals($x, $cache->get($key));
        $cache->delete($key);
        $this->assertEquals(null, $cache->get($key));

        // Empty key
        $key = '';
        $x = 'blah';
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

        // Big value size is computed AFTER compress if compress on
        $s = str_repeat('0', 1200000);
        $this->reflectionGet($cache, 'memcached')
            ->setOption(\Memcached::OPT_COMPRESSION, false);
        $cache->setConfig('memcachedAutoSplit', 0);
        // Error: Memcache set error 10: SERVER ERROR
        @$cache->set($key, $s, 3600);
        $this->assertEquals(null, $cache->get($key));
        $this->reflectionGet($cache, 'memcached')
            ->setOption(\Memcached::OPT_COMPRESSION, true);
        $cache->set($key, $s, 3600);
        $this->assertEquals($s, $cache->get($key));
    }


    /**
     * create()
     */
    public function testCreate()
    {
        $cache = Cache::create('memcached');

        // Server list is empty now
        $cache->get('any key');
        $ar = $this->reflectionGet($cache, 'memcached')->getServerList();
        $this->assertEquals($ar, []);

        $this->assertInstanceOf(
            MemcachedHandler::class,
            $cache->setConfigServer()
        );
    }


    /**
     * Disable to eliminate output by error_log()
     */
    public function tesSetConfigServer()
    {
        $cache = $this->buildMock();

        // This should be a valid server
        $ms = GlobalConfig::getInstance()->get('memcached.server');

        // Multi server, one of them is dead
        $x = [
            // Dead one
            [
                'host'      => $ms[0]['host'],
                'port'      => 80,
                'weight'    => 67,
            ],
            // Alive one
            [
                'host'      => $ms[0]['host'],
                'port'      => $ms[0]['port'],
                'weight'    => 33
            ],
        ];
        $cache->setConfigServer($x);

        $memcached = $this->reflectionGet($cache, 'memcached');
        $this->assertEquals(
            [$x[1]],
            $memcached->getServerList()
        );
    }
}
