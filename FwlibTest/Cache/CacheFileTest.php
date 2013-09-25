<?php
namespace FwlibTest\Cache;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Util\FileSystem;

/**
 * Test for Fwlib\Cache\CacheFile
 *
 * Some test fle/path environment is for *nix only.
 *
 * @package     FwlibTest\Cache
 * @copyright   Copyright 2012-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-11-06
 */
class CacheFileTest extends PHPunitTestCase
{
    /**
     * Cache object
     *
     * @var Fwlib\Cache\CacheFile
     */
    protected $ch = null;

    public function __construct()
    {
        $this->ch = Cache::create('file');
    }


    /**
     * get(), set(), del(), expire() etc
     */
    public function testCache()
    {
        $this->ch->setConfig(array('cache-file-rule' => '55'));
        $key = 'site/index';
        // '/tmp/cache/89/3ed0dc6e'
        $x = $this->ch->getFilePath($key);

        // Clean test dir
        $y = FileSystem::getDirName($x);
        if (file_exists($y)) {
            FileSystem::del($y);
        }

        // Cache set
        $v = 'blah';
        $this->ch->setConfig('cache-store-method', 1);
        $this->ch->set($key, $v);
        $this->assertEquals(json_encode($v), file_get_contents($x));

        // Cache expire
        $this->assertEquals(true, $this->ch->expire($key, -10));
        $this->assertEquals(true, $this->ch->expire($key, strtotime('2012-1-1')));
        $this->assertEquals(false, $this->ch->expire($key, 10));
        $this->assertEquals(false, $this->ch->expire($key, 1));
        $this->assertEquals(false, $this->ch->expire($key, 0));
        $this->assertEquals(false, $this->ch->expire($key, null));

        // Cache get
        $this->assertEquals($v, $this->ch->get($key));
        $this->assertEquals(null, $this->ch->get($key, -10));
        $this->assertEquals($v, $this->ch->get($key, 0));
        $this->assertEquals($v, $this->ch->get($key, 5));
        $this->assertEquals($v, $this->ch->get($key, null));

        $v = '你好';
        $this->ch->setConfig('cache-store-method', 0);
        $this->ch->set($key, $v);
        $this->assertEquals($v, $this->ch->get($key));

        $v = array('你' => '好');
        $this->ch->setConfig('cache-store-method', 1);
        $this->ch->set($key, $v);
        $this->assertEquals($v, $this->ch->get($key));

        // Cache del
        $this->ch->del($key);
        $this->assertEquals(null, $this->ch->get($key));
    }


    /**
     * create(), checkConfig()
     */
    public function testCreate()
    {
        $ch = Cache::create('file');

        $ch->setConfig('cache-file-dir', '');
        $this->assertFalse($ch->checkConfig());
        $this->assertEquals(
            'No cache file dir defined.',
            $ch->errorMsg
        );

        $ch->setConfig('cache-file-rule', '');
        $this->assertFalse($ch->checkConfig());
        $this->assertEquals(
            'No cache file rule defined.',
            $ch->errorMsg
        );

        // Wrong config
        $ch->setConfig('cache-file-dir', '/proc/');
        $this->assertEquals(false, $ch->checkConfig());

        $ch->setConfig('cache-file-rule', '8');
        $this->assertEquals(false, $ch->checkConfig());

        $ch->setConfig('cache-file-rule', '0blah');
        $this->assertEquals(false, $ch->checkConfig());

        // Create file dir fail
        $ch->setConfig('cache-file-dir', '/var/log/test-cache-tmp/');
        // Hide error: mkdir(): Permission denied
        $this->assertEquals(false, @$ch->checkConfig());
    }


    public function testGetFilePath()
    {
        $this->ch->setConfig('cache-file-dir', '/tmp/cache/');
        $this->ch->setConfig('cache-file-rule', '1140');
        $key = 'site/index';

        $x = '/tmp/cache/d0/ex/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        $this->ch->setConfig(array('cache-file-rule' => ''));
        $x = '/tmp/cache/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        $this->ch->setConfig(array('cache-file-rule' => '1131'));
        $x = '/tmp/cache/d0/te/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Notice: Directly use key's part as path may cause wrong
        $this->ch->setConfig(array('cache-file-rule' => '2342'));
        $x = '/tmp/cache/57//i/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage
        $this->ch->setConfig(array('cache-file-rule' => '1011'));
        $x = '/tmp/cache/3e/d0/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage 2
        $this->ch->setConfig(array('cache-file-rule' => '2021'));
        $x = '/tmp/cache/b6/9c/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage 3
        $this->ch->setConfig(array('cache-file-rule' => '55'));
        $x = '/tmp/cache/89/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);
    }
}
