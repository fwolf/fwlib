<?php
namespace FwlibTest\Mvc;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Mvc\AbstractModel;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractModelTest extends PHPunitTestCase
{
    protected static $cache = null;
    public static $dummyMethod = '';
    public static $forceRefreshCache = false;


    public function __construct()
    {
        self::$cache = Cache::create();
    }


    protected function buildMock()
    {
        $model = $this->getMock(
            'Fwlib\Mvc\AbstractModel',
            array('dummyMethod', 'getCache')
        );

        $model->expects($this->any())
            ->method('dummyMethod')
            ->will($this->returnArgument(0));

        // Empty cache log for check later
        $this->reflectionSet(self::$cache, 'log', array());

        $model->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue(self::$cache));

        return $model;
    }


    protected function buildMockWithForceRefreshCache()
    {
        $model = $this->getMock(
            'Fwlib\Mvc\AbstractModel',
            array('dummyMethod', 'forceRefreshCache', 'getCache')
        );

        $model->expects($this->any())
            ->method('dummyMethod')
            ->will($this->returnArgument(0));

        $model->expects($this->any())
            ->method('forceRefreshCache')
            ->will($this->returnCallback(function () {
                return AbstractModelTest::$forceRefreshCache;
            }));

        // Empty cache log for check later
        $this->reflectionSet(self::$cache, 'log', array());

        $model->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue(self::$cache));

        return $model;
    }


    public function testCachedCall()
    {
        $model = $this->buildMock();

        $model->setUseCache(false);
        $this->assertFalse($model->getUseCache());

        // Not using cache
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $this->assertEmpty(self::$cache->getLog());


        $model->setUseCache(true);
        $this->assertTrue($model->getUseCache());

        // Use cache, the first get from cache will fail
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);


        // Then the second get from cache will success
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertTrue($cacheLog['success']);
        $this->assertStringEndsWith(
            '/dummyMethod/foo',
            $cacheLog['key']
        );


        // Change key, cache get will fail again
        $model->cachedCall('dummyMethod', array('bar'));
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);
    }


    public function testCachedCallWithArrayParam()
    {
        $model = $this->buildMock();

        $model->setUseCache(true);

        // Use cache, the first get from cache will fail
        $this->assertEqualArray(
            array('foo', 'bar'),
            $model->cachedCall('dummyMethod', array(array('foo', 'bar')))
        );
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);


        // Then the second get from cache will success
        $this->assertEqualArray(
            array('foo', 'bar'),
            $model->cachedCall('dummyMethod', array(array('foo', 'bar')))
        );
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertTrue($cacheLog['success']);
        $this->assertStringEndsWith(
            '/dummyMethod/0/foo/1/bar',
            $cacheLog['key']
        );


        // Change key, cache get will fail again
        $model->cachedCall('dummyMethod', array(array('bar', 'foo')));
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);
    }


    public function testCachedCallWithForceRefreshCache()
    {
        $model = $this->buildMockWithForceRefreshCache();

        $model->setUseCache(true);
        self::$forceRefreshCache = true;

        // The get will not go through cache
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $this->assertEmpty(self::$cache->getLog());
    }


    public function testCachedCallWithObjectParam()
    {
        $model = $this->buildMock();

        $model->setUseCache(true);

        // Use $cache as object param

        // Use cache, the first get from cache will fail
        $this->assertEquals(
            self::$cache,
            $model->cachedCall('dummyMethod', array(self::$cache))
        );
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);
        $this->assertStringStartsWith(
            '/dummyMethod/Cache/',
            strstr($cacheLog['key'], '/dummyMethod/')
        );


        // Then the second get from cache will success
        // The decoded return value is different with original object, but
        // this is not what we test for, and json_decode itself can't keep
        // same with object before json_encode. So we check log success only.
        $model->cachedCall('dummyMethod', array(self::$cache));
        $cacheLog = self::$cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertTrue($cacheLog['success']);
    }
}
