<?php
namespace FwlibTest\Cache;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Cache\CachedCaller;
use Fwlib\Cache\AbstractCachedCallerAware;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractCachedCallerAwareTest extends PHPunitTestCase
{
    /**
     * @return  AbstractCachedCallerAware
     */
    protected function buildMock()
    {
        $model = $this->getMock(
            'Fwlib\Cache\AbstractCachedCallerAware',
            array('callMe', 'getCacheLifetime')
        );

        $model->expects($this->atMost(1))
            ->method('callMe')
            ->will($this->returnCallback(function () {
                return microtime(false);
            }));

        $model->expects($this->any())
            ->method('getCacheLifetime')
            ->will($this->returnValue(300));

        return $model;
    }


    public function testCall()
    {
        $model = $this->buildMock();
        $model->setUseCache(true)
            ->setForceRefreshCache(false);

        $cachedCaller = new CachedCaller;
        $cachedCaller->setHandler(new Cache);

        // The 2nd call should read from cache, callMe() is called only once
        $resultOne = $cachedCaller->call($model, 'callMe', array(42));
        $resultTwo = $cachedCaller->call($model, 'callMe', array(42));

        $this->assertEquals($resultOne, $resultTwo);
    }


    public function testGetCacheKey()
    {
        $model = $this->buildMock();

        $key = $model->getCacheKey('callMe', array());
        // Class of mocked object is Mock_AbstractCachedCallerAwareModel_67d22466
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe', $key);


        $key = $model->getCacheKey('callMe', array('foo', 'bar'));
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/foo/bar', $key);


        $key = $model->getCacheKey('callMe', array(array('foo', 'bar')));
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/0/foo/1/bar', $key);


        $key = $model->getCacheKey('callMe', array(array(array('f' => 'b'))));
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/0/12cf2842', $key);


        $key = $model->getCacheKey('callMe', array(new \stdClass));
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/stdClass/99914b93', $key);
    }
}