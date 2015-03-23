<?php
namespace FwlibTest\Cache\Handler;

use Fwlib\Cache\Cache;
use Fwlib\Cache\Handler\File as FileHandler;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * Some test file/path environment is for *nix only.
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FileTest extends PHPUnitTestCase
{
    use UtilContainerAwareTrait;


    /**
     * @return FileHandler
     */
    protected function buildMock()
    {
        return Cache::create('file');
    }


    /**
     * get(), set(), delete(), expire() etc
     */
    public function testCache()
    {
        $cache = $this->buildMock();

        $cache->setConfigs(['fileRule' => '55']);
        $key = 'site/index';
        // '/tmp/cache/89/3ed0dc6e'
        $x = $cache->getFilePath($key);

        // Clean test dir
        $fileSystem = $this->getUtilContainer()->getFileSystem();
        $y = $fileSystem->getDirName($x);
        if (file_exists($y)) {
            $fileSystem->del($y);
        }

        // Cache set
        $v = 'blah';
        $cache->setConfig('storeMethod', 1);
        $cache->set($key, $v);
        $this->assertEquals(json_encode($v), file_get_contents($x));

        // Cache expire
        $this->assertTrue(
            $this->reflectionCall($cache, 'isExpired', [$key, -10])
        );
        $this->assertTrue(
            $this->reflectionCall($cache, 'isExpired', [$key, strtotime('2012-1-1')])
        );

        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key, 10])
        );
        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key, 1])
        );
        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key, 0])
        );
        $this->assertFalse(
            $this->reflectionCall($cache, 'isExpired', [$key, null])
        );

        // Cache get
        $this->assertEquals($v, $cache->get($key));
        $this->assertEquals(null, $cache->get($key, -10));
        $this->assertEquals($v, $cache->get($key, 0));
        $this->assertEquals($v, $cache->get($key, 5));
        $this->assertEquals($v, $cache->get($key, null));

        $v = '你好';
        $cache->setConfig('storeMethod', 0);
        $cache->set($key, $v);
        $this->assertEquals($v, $cache->get($key));

        $v = ['你' => '好'];
        $cache->setConfig('storeMethod', 1);
        $cache->set($key, $v);
        $this->assertEquals($v, $cache->get($key));

        // Cache delete
        $cache->delete($key);
        $this->assertEquals(null, $cache->get($key));
    }


    /**
     * create(), checkConfig()
     */
    public function testCreate()
    {
        /** @var FileHandler $cache */
        $cache = Cache::create('file');

        $cache->setConfig('fileDir', '');
        $this->assertFalse($cache->checkConfig());
        $this->assertEquals(
            'No cache file dir defined.',
            $cache->getErrorMessage()
        );

        $cache->setConfig('fileRule', '');
        $this->assertFalse($cache->checkConfig());
        $this->assertEquals(
            'No cache file rule defined.',
            $cache->getErrorMessage()
        );

        // Wrong config
        $cache->setConfig('fileDir', '/proc/');
        $this->assertEquals(false, $cache->checkConfig());

        $cache->setConfig('fileRule', '8');
        $this->assertEquals(false, $cache->checkConfig());

        $cache->setConfig('fileRule', '0blah');
        $this->assertEquals(false, $cache->checkConfig());

        // Create file dir fail
        $cache->setConfig('fileDir', '/var/log/test-cache-tmp/');
        // Hide error: mkdir(): Permission denied
        $this->assertEquals(false, @$cache->checkConfig());
    }


    public function testGetFilePath()
    {
        $cache = $this->buildMock();

        $cache->setConfig('fileDir', '/tmp/cache/');
        $cache->setConfig('fileRule', '1140');
        $key = 'site/index';

        $x = '/tmp/cache/d0/ex/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);

        $cache->setConfigs(['fileRule' => '']);
        $x = '/tmp/cache/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);

        $cache->setConfigs(['fileRule' => '1131']);
        $x = '/tmp/cache/d0/te/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);

        // Notice: Directly use key's part as path may cause wrong
        $cache->setConfigs(['fileRule' => '2342']);
        $x = '/tmp/cache/57//i/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage
        $cache->setConfigs(['fileRule' => '1011']);
        $x = '/tmp/cache/3e/d0/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage 2
        $cache->setConfigs(['fileRule' => '2021']);
        $x = '/tmp/cache/b6/9c/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);

        // Common usage 3
        $cache->setConfigs(['fileRule' => '55']);
        $x = '/tmp/cache/89/3ed0dc6e';
        $y = $cache->getFilePath($key);
        $this->assertEquals($x, $y);
    }
}
