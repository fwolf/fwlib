<?php
namespace FwlibTest\Cache;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Util\UtilContainer;

/**
 * Some test file/path environment is for *nix only.
 *
 * @copyright   Copyright 2012-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CacheFileTest extends PHPUnitTestCase
{
    /**
     * Cache object
     *
     * @var Fwlib\Cache\CacheFile
     */
    protected $ch = null;

    protected $utilContainer = null;


    public function __construct()
    {
        $this->ch = Cache::create('file');
        $this->utilContainer = UtilContainer::getInstance();
    }


    /**
     * get(), set(), delete(), expire() etc
     */
    public function testCache()
    {
        $this->ch->setConfig(['fileRule' => '55']);
        $key = 'site/index';
        // '/tmp/cache/89/3ed0dc6e'
        $x = $this->ch->getFilePath($key);

        // Clean test dir
        $fileSystem = $this->utilContainer->get('FileSystem');
        $y = $fileSystem->getDirName($x);
        if (file_exists($y)) {
            $fileSystem->del($y);
        }

        // Cache set
        $v = 'blah';
        $this->ch->setConfig('storeMethod', 1);
        $this->ch->set($key, $v);
        $this->assertEquals(json_encode($v), file_get_contents($x));

        // Cache expire
        $this->assertTrue(
            $this->reflectionCall($this->ch, 'isExpired', [$key, -10])
        );
        $this->assertTrue(
            $this->reflectionCall($this->ch, 'isExpired', [$key, strtotime('2012-1-1')])
        );

        $this->assertFalse(
            $this->reflectionCall($this->ch, 'isExpired', [$key, 10])
        );
        $this->assertFalse(
            $this->reflectionCall($this->ch, 'isExpired', [$key, 1])
        );
        $this->assertFalse(
            $this->reflectionCall($this->ch, 'isExpired', [$key, 0])
        );
        $this->assertFalse(
            $this->reflectionCall($this->ch, 'isExpired', [$key, null])
        );

        // Cache get
        $this->assertEquals($v, $this->ch->get($key));
        $this->assertEquals(null, $this->ch->get($key, -10));
        $this->assertEquals($v, $this->ch->get($key, 0));
        $this->assertEquals($v, $this->ch->get($key, 5));
        $this->assertEquals($v, $this->ch->get($key, null));

        $v = '你好';
        $this->ch->setConfig('storeMethod', 0);
        $this->ch->set($key, $v);
        $this->assertEquals($v, $this->ch->get($key));

        $v = ['你' => '好'];
        $this->ch->setConfig('storeMethod', 1);
        $this->ch->set($key, $v);
        $this->assertEquals($v, $this->ch->get($key));

        // Cache delete
        $this->ch->delete($key);
        $this->assertEquals(null, $this->ch->get($key));
    }


    /**
     * create(), checkConfig()
     */
    public function testCreate()
    {
        $ch = Cache::create('file');

        $ch->setConfig('fileDir', '');
        $this->assertFalse($ch->checkConfig());
        $this->assertEquals(
            'No cache file dir defined.',
            $ch->getErrorMessage()
        );

        $ch->setConfig('fileRule', '');
        $this->assertFalse($ch->checkConfig());
        $this->assertEquals(
            'No cache file rule defined.',
            $ch->getErrorMessage()
        );

        // Wrong config
        $ch->setConfig('fileDir', '/proc/');
        $this->assertEquals(false, $ch->checkConfig());

        $ch->setConfig('fileRule', '8');
        $this->assertEquals(false, $ch->checkConfig());

        $ch->setConfig('fileRule', '0blah');
        $this->assertEquals(false, $ch->checkConfig());

        // Create file dir fail
        $ch->setConfig('fileDir', '/var/log/test-cache-tmp/');
        // Hide error: mkdir(): Permission denied
        $this->assertEquals(false, @$ch->checkConfig());
    }


    public function testGetFilePath()
    {
        $this->ch->setConfig('fileDir', '/tmp/cache/');
        $this->ch->setConfig('fileRule', '1140');
        $key = 'site/index';

        $x = '/tmp/cache/d0/ex/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        $this->ch->setConfig(['fileRule' => '']);
        $x = '/tmp/cache/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        $this->ch->setConfig(['fileRule' => '1131']);
        $x = '/tmp/cache/d0/te/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Notice: Directly use key's part as path may cause wrong
        $this->ch->setConfig(['fileRule' => '2342']);
        $x = '/tmp/cache/57//i/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage
        $this->ch->setConfig(['fileRule' => '1011']);
        $x = '/tmp/cache/3e/d0/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage 2
        $this->ch->setConfig(['fileRule' => '2021']);
        $x = '/tmp/cache/b6/9c/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage 3
        $this->ch->setConfig(['fileRule' => '55']);
        $x = '/tmp/cache/89/3ed0dc6e';
        $y = $this->ch->getFilePath($key);
        $this->assertEquals($x, $y);
    }
}
