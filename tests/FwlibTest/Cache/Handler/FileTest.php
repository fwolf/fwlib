<?php
namespace FwlibTest\Cache\Handler;

use Fwlib\Cache\Handler\File as FileHandler;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     * @return MockObject | FileHandler
     */
    protected function buildMock()
    {
        $mock = $this->getMock(FileHandler::class, null);

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        vfsStream::setup('FileTest');
    }


    /**
     * get(), set(), delete(), expire() etc
     *
     * Flag LOCK_EX is used when write file, vfs can not mock this.
     * @see https://github.com/mikey179/vfsStream/wiki/Known-Issues
     */
    public function test()
    {
        $handler = $this->buildMock();

        $handler->setConfig('fileDir', '/tmp/cache/');
        $handler->setConfigs(['fileRule' => '55']);
        $key = 'site/index';
        // '/tmp/cache/89/3ed0dc6e'
        $filePath = $this->reflectionCall($handler, 'getFilePath', [$key]);

        // Clean test dir
        $fileSystem = $this->getUtilContainer()->getFileSystem();
        $fileDir = $fileSystem->getDirName($filePath);
        if (file_exists($fileDir)) {
            $fileSystem->del($fileDir);
        }

        // Cache set
        $value = 'blah';
        $handler->setConfig('storeMethod', 1);
        $handler->set($key, $value);
        $this->assertEquals($value, file_get_contents($filePath));

        // Cache expire
        $this->assertTrue($handler->isExpired($key, -10));
        $this->assertFalse($handler->isExpired($key, 10));
        $this->assertFalse($handler->isExpired($key, 1));
        $this->assertFalse($handler->isExpired($key, 0));
        $this->assertFalse($handler->isExpired($key, null));

        // Cache get
        $this->assertEquals($value, $handler->get($key));
        $this->assertEquals(null, $handler->get($key, -10));
        $this->assertEquals($value, $handler->get($key, 0));
        $this->assertEquals($value, $handler->get($key, 5));
        $this->assertEquals($value, $handler->get($key, null));

        $value = '你好';
        $handler->set($key, $value);
        $this->assertEquals($value, $handler->get($key));

        // Cache delete
        $handler->delete($key);
        $this->assertEquals(null, $handler->get($key));
    }


    public function testCheckConfig()
    {
        $handler = $this->buildMock();

        $handler->setConfig('fileDir', '');
        $handler->setConfig('fileRule', '');
        $this->assertFalse($handler->checkConfig());
        $this->assertEquals(2, count($handler->getErrorMessages()));


        $readonlyDir = vfsStream::url('FileTest/readonlyDir/');
        mkdir($readonlyDir, 0555);

        $handler->setConfig('fileDir', $readonlyDir . 'foo/');
        $handler->setConfig('fileRule', '1');
        $this->assertFalse($handler->checkConfig());
        $this->assertEquals(2, count($handler->getErrorMessages()));


        $handler->setConfig('fileDir', $readonlyDir);
        $handler->setConfig('fileRule', '111');
        $this->assertFalse($handler->checkConfig());
        $this->assertEquals(2, count($handler->getErrorMessages()));


        $writableDir = vfsStream::url('FileTest/writableDir/');
        mkdir($writableDir, 0755);

        $handler->setConfig('fileDir', $writableDir);
        $handler->setConfig('fileRule', '10');
        $this->assertTrue($handler->checkConfig());
        $this->assertEquals(0, count($handler->getErrorMessages()));
    }


    public function testGetFilePath()
    {
        $handler = $this->buildMock();

        $handler->setConfig('fileDir', '/tmp/cache/');
        $handler->setConfig('fileRule', '1140');
        $key = 'site/index';

        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/d0/ex/3ed0dc6e', $path);

        $handler->setConfigs(['fileRule' => '']);
        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/3ed0dc6e', $path);

        $handler->setConfigs(['fileRule' => '1131']);
        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/d0/te/3ed0dc6e', $path);

        // Notice: Directly use key's part as path may cause wrong
        $handler->setConfigs(['fileRule' => '2342']);
        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/57//i/3ed0dc6e', $path);

        // Common usage
        $handler->setConfigs(['fileRule' => '1011']);
        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/3e/d0/3ed0dc6e', $path);

        // Common usage 2
        $handler->setConfigs(['fileRule' => '2021']);
        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/b6/9c/3ed0dc6e', $path);

        // Common usage 3
        $handler->setConfigs(['fileRule' => '55']);
        $path = $this->reflectionCall($handler, 'getFilePath', [$key]);
        $this->assertEquals('/tmp/cache/89/3ed0dc6e', $path);
    }
}
