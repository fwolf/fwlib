<?php
namespace Fwlib\Mvc\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Mvc\AbstractModel;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-06
 */
class AbstractModelTest extends PHPunitTestCase
{
    protected $serviceContainer;
    protected $model;
    public static $dummyMethod = '';
    public static $forceRefreshCache = false;


    public function __construct()
    {
        $this->serviceContainer = ServiceContainerTest::getInstance();

        $this->serviceContainer->registerInstance('Cache', Cache::create(''));

        $this->model = $this->buildMock();
    }


    protected function buildMock()
    {
        $model = $this->getMock(
            'Fwlib\Mvc\AbstractModel',
            array('dummyMethod')
        );

        $model->setServiceContainer($this->serviceContainer);

        $model->expects($this->any())
            ->method('dummyMethod')
            ->will($this->returnArgument(0));

        return $model;
    }


    protected function buildMockWithForceRefreshCache()
    {
        $model = $this->getMock(
            'Fwlib\Mvc\AbstractModel',
            array('dummyMethod', 'forceRefreshCache')
        );

        $model->setServiceContainer($this->serviceContainer);

        $model->expects($this->any())
            ->method('dummyMethod')
            ->will($this->returnArgument(0));

        $model->expects($this->any())
            ->method('forceRefreshCache')
            ->will($this->returnCallback(function () {
                return AbstractModelTest::$forceRefreshCache;
            }));

        return $model;
    }


    public function testCachedCall()
    {
        $model = $this->model;
        $cache = $this->serviceContainer->get('Cache');
        // Empty cache log for check later
        $this->reflectionSet($cache, 'log', array());

        $model->setUseCache(false);
        $this->assertFalse($model->getUseCache());

        // Not using cache
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $this->assertEmpty($cache->getLog());


        $model->setUseCache(true);
        $this->assertTrue($model->getUseCache());

        // Use cache, the first get from cache will fail
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);


        // Then the second get from cache will success
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertTrue($cacheLog['success']);
        $this->assertStringEndsWith(
            '/dummyMethod/foo',
            $cacheLog['key']
        );


        // Change key, cache get will fail again
        $model->cachedCall('dummyMethod', array('bar'));
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);
    }


    public function testCachedCallWithArrayParam()
    {
        $model = $this->model;
        $cache = $this->serviceContainer->get('Cache');

        $model->setUseCache(true);

        // Use cache, the first get from cache will fail
        $this->assertEqualArray(
            array('foo', 'bar'),
            $model->cachedCall('dummyMethod', array(array('foo', 'bar')))
        );
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);


        // Then the second get from cache will success
        $this->assertEqualArray(
            array('foo', 'bar'),
            $model->cachedCall('dummyMethod', array(array('foo', 'bar')))
        );
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertTrue($cacheLog['success']);
        $this->assertStringEndsWith(
            '/dummyMethod/0/foo/1/bar',
            $cacheLog['key']
        );


        // Change key, cache get will fail again
        $model->cachedCall('dummyMethod', array(array('bar', 'foo')));
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertFalse($cacheLog['success']);
    }


    public function testCachedCallWithForceRefreshCache()
    {
        $model = $this->buildMockWithForceRefreshCache();
        $cache = $this->serviceContainer->get('Cache');

        $model->setUseCache(true);
        self::$forceRefreshCache = true;
        // Empty cache log for check later
        $this->reflectionSet($cache, 'log', array());

        // The get will not go through cache
        $this->assertEquals(
            'foo',
            $model->cachedCall('dummyMethod', array('foo'))
        );
        $this->assertEmpty($cache->getLog());
    }


    public function testCachedCallWithObjectParam()
    {
        $model = $this->model;
        $cache = $this->serviceContainer->get('Cache');

        $model->setUseCache(true);

        // Use $cache as object param

        // Use cache, the first get from cache will fail
        $this->assertEquals(
            $cache,
            $model->cachedCall('dummyMethod', array($cache))
        );
        $cacheLog = $cache->getLog();
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
        $model->cachedCall('dummyMethod', array($cache));
        $cacheLog = $cache->getLog();
        $cacheLog = array_pop($cacheLog);
        $this->assertTrue($cacheLog['success']);
    }


    public function testGetDb()
    {
        $model = $this->model;

        $this->assertInstanceOf(
            'Fwlib\Bridge\Adodb',
            $this->reflectionCall($model, 'getDb')
        );
    }
}
