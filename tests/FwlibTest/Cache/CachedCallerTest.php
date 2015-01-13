<?php
namespace FwlibTest\Cache;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\CacheInterface;
use Fwlib\Cache\CachedCaller;
use Fwlib\Cache\CachedCallerAwareInterface;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CachedCallerTest extends PHPUnitTestCase
{
    public static $isForceRefreshCache = false;
    public static $isUseCache = true;


    /**
     * @return  CacheInterface
     */
    protected function buildCacheHandlerMock()
    {
        $cache = $this->getMock(
            'Fwlib\Cache\Cache',
            null
        );

        return $cache;
    }


    /**
     * @return  CachedCallerAwareInterface
     */
    protected function buildCachedCallerAwareMock()
    {
        $dummy = $this->getMock(
            'Fwlib\Cache\CachedCallerAwareInterface',
            array(
                'callMe',
                'getCacheKey',
                'getCacheLifetime',
                'isForceRefreshCache',
                'isUseCache',
            )
        );

        $dummy->expects($this->any())
            ->method('callMe')
            ->will($this->returnValue('not cached'));

        $dummy->expects($this->any())
            ->method('getCacheKey')
            ->will($this->returnValue('cacheKey'));

        $dummy->expects($this->any())
            ->method('isForceRefreshCache')
            ->will($this->returnCallback(function () {
                return CachedCallerTest::$isForceRefreshCache;
            }));

        $dummy->expects($this->any())
            ->method('isUseCache')
            ->will($this->returnCallback(function () {
                return CachedCallerTest::$isUseCache;
            }));

        return $dummy;
    }


    /**
     * @return  CachedCaller
     */
    protected function buildMock()
    {
        $cachedCaller = $this->getMock(
            'Fwlib\Cache\CachedCaller',
            null
        );

        /** @type CachedCaller $cachedCaller */
        $cachedCaller->setHandler($this->buildCacheHandlerMock());

        return $cachedCaller;
    }


    public function testCall()
    {
        $cachedCaller = $this->buildMock();
        $dummy = $this->buildCachedCallerAwareMock();


        // Call without use cache
        self::$isUseCache = false;
        $rs = $cachedCaller->call($dummy, 'callMe');
        $this->assertEquals('not cached', $rs);


        // Call with force refresh/update
        self::$isUseCache = true;
        self::$isForceRefreshCache = true;

        $cacheHandler = $this->reflectionGet($cachedCaller, 'handler');
        $cacheHandler->set('cacheKey', 'cached');

        $rs = $cachedCaller->call($dummy, 'callMe');
        $this->assertEquals('not cached', $rs);


        // Call with read from cache
        self::$isUseCache = true;
        self::$isForceRefreshCache = false;

        $cacheHandler->set('cacheKey', 'cached');

        $rs = $cachedCaller->call($dummy, 'callMe');
        $this->assertEquals('cached', $rs);
    }
}
