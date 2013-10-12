<?php
namespace Fwlib\Cache\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Config\ConfigGlobal;

/**
 * Test for Fwlib\Cache\CacheMemcached
 *
 * @requires extension memcached
 *
 * @package     Fwlib\Cache\Test
 * @copyright   Copyright 2012-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-11-06
 */
class CacheMemcachedTest extends PHPunitTestCase
{
    /**
     * Cache object
     *
     * @var Fwlib\Cache\CacheMemcached
     */
    protected $ch = null;

    public function __construct()
    {
        $this->ch = Cache::create('memcached');
    }


    /**
     * get(), set(), del(), expire() etc
     */
    public function testCache()
    {
        // This should be a valid server
        $ms = ConfigGlobal::get('memcached.server');


        // Memcache server recognize by array position, not assoc key
        $x = array(
            'h' => $ms[0]['host'],
            'p' => $ms[0]['port'],
            'w' => $ms[0]['weight'],
        );
        $y = array(
            'host' => $ms[0]['host'],
            'port' => $ms[0]['port'],
            'weight' => $ms[0]['weight'],
        );
        $this->ch->setConfigServer($x);
        $this->assertEquals(array($y), $this->ch->memcached->getServerList());


        // Set server cfg, writable
        $this->ch->setConfigServer($ms);
        unset($this->ch->memcached);
        $ar = $this->ch->memcached->getServerList();
        $this->assertEquals($ar, $ms);


        // Cache write
        $key = str_repeat('test', 8);
        $x = 'blah';
        $this->ch->set($key, $x, 60);
        $this->assertEquals($x, $this->ch->get($key));

        $x = array('blah', array('foo' => 'boo'));
        $this->ch->set($key, $x, 60);
        $this->assertEquals($x, $this->ch->get($key));

        // Cache expire
        $this->ch->setConfig('cache-memcached-autosplit', 1);
        $this->ch->set($key, $x, 60);
        $this->assertEquals(false, $this->ch->expire($key));
        $this->ch->del($key);
        $this->assertTrue($this->ch->expire($key));
        $this->ch->set($key, $x, -10);
        $this->assertEquals(true, $this->ch->expire($key));
        $this->ch->setConfig('cache-memcached-autosplit', 0);
        $this->ch->set($key, $x, 60);
        $this->assertEquals(false, $this->ch->expire($key));
        $this->ch->set($key, $x, -10);
        $this->assertEquals(true, $this->ch->expire($key));

        // Cache del
        $this->ch->del($key);
        $this->assertEquals(null, $this->ch->get($key));

        // Long key
        $key = str_repeat('-', 300);
        $x = 'blah';
        $this->ch->set($key, $x, 60);
        $this->assertEquals($x, $this->ch->get($key));
        $this->ch->del($key);
        $this->assertEquals(null, $this->ch->get($key));

        // Empty key
        $key = '';
        $x = 'blah';
        $this->ch->set($key, $x, 60);
        $this->assertEquals($x, $this->ch->get($key));

        // Cache get with expire
        $key = str_repeat('test', 8);
        $this->ch->set($key, $x, -10);
        $this->assertEquals(null, $this->ch->get($key));
        $this->ch->set($key, $x, 0);
        $this->assertEquals($x, $this->ch->get($key));
        $this->ch->set($key, $x, 5);
        $this->assertEquals($x, $this->ch->get($key));
        $this->ch->set($key, $x, null);
        $this->assertEquals($x, $this->ch->get($key));


        // Big value exceed max item size
        $this->ch->setConfig('cache-memcached-maxitemsize', 100);

        $s = str_repeat('0', 300);
        $this->ch->del($key);       // Clear previous setted value
        $this->ch->setConfig('cache-memcached-autosplit', 1);
        $this->ch->set($key, $s, 3600);
        $this->assertEquals($s, $this->ch->get($key));
        $this->assertEquals(false, $this->ch->expire($key));
        $this->ch->del($key);
        $this->assertEquals(null, $this->ch->get($key));
        $this->assertEquals(true, $this->ch->expire($key));

        // Big value size is computed AFTER compress if compress on
        $s = str_repeat('0', 1200000);
        $this->ch->memcached->setOption(\Memcached::OPT_COMPRESSION, false);
        $this->ch->setConfig('cache-memcached-autosplit', 0);
        // Error: Memcache set error 10: SERVER ERROR
        @$this->ch->set($key, $s, 3600);
        $this->assertEquals(null, $this->ch->get($key));
        $this->ch->memcached->setOption(\Memcached::OPT_COMPRESSION, true);
        $this->ch->set($key, $s, 3600);
        $this->assertEquals($s, $this->ch->get($key));
    }


    /**
     * create()
     */
    public function testCreate()
    {
        $ch = Cache::create('memcached');

        // Server list is empty now
        $ar = $this->ch->memcached->getServerList();
        $this->assertEquals($ar, array());

        $this->assertInstanceOf(
            'Fwlib\Cache\CacheMemcached',
            $this->ch->setConfigServer()
        );
    }


    /**
     * Disable to eliminate output by error_log()
     */
    public function tesSetConfigServer()
    {
        // This should be a valid server
        $ms = ConfigGlobal::get('memcached.server');

        // Multi server, one of them is dead
        $x = array(
            // Dead one
            array(
                'host'      => $ms[0]['host'],
                'port'      => 80,
                'weight'    => 67,
            ),
            // Alive one
            array(
                'host'      => $ms[0]['host'],
                'port'      => $ms[0]['port'],
                'weight'    => 33
            ),
        );
        $this->ch->setConfigServer($x);
        $this->assertEquals(
            array($x[1]),
            $this->ch->memcached->getServerList()
        );
    }
}
