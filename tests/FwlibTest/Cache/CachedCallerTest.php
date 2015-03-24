<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\CachedCaller;
use Fwlib\Cache\CachedCallerAwareInterface;
use Fwlib\Cache\Handler\PhpArray as PhpArrayHandler;
use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CachedCallerTest extends PHPUnitTestCase
{
    /** @var mixed */
    protected $callMe = null;

    /** @var bool */
    protected $isForceRefreshCache = false;

    /** @var bool */
    protected $isUseCache = true;


    /**
     * @return  CacheHandlerInterface
     */
    protected function buildCacheHandlerMock()
    {
        $mock = $this->getMock(
            PhpArrayHandler::class,
            null
        );

        return $mock;
    }


    /**
     * @return  CachedCallerAwareInterface
     */
    protected function buildCachedCallerAwareMock()
    {
        $mock = $this->getMock(
            CachedCallerAwareInterface::class,
            [
                'callMe',
                'getCacheKey',
                'getCacheLifetime',
                'isForceRefreshCache',
                'isUseCache',
                'setForceRefreshCache',
                'setUseCache',
            ]
        );

        $mock->expects($this->any())
            ->method('callMe')
            ->will($this->returnCallback(function () {
                return $this->callMe;
            }));

        $mock->expects($this->any())
            ->method('getCacheKey')
            ->will($this->returnValue('cacheKey'));

        $mock->expects($this->any())
            ->method('isForceRefreshCache')
            ->will($this->returnCallback(function () {
                return $this->isForceRefreshCache;
            }));

        $mock->expects($this->any())
            ->method('isUseCache')
            ->will($this->returnCallback(function () {
                return $this->isUseCache;
            }));

        return $mock;
    }


    /**
     * @return  CachedCaller
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            CachedCaller::class,
            null
        );

        /** @type CachedCaller $mock */
        $mock->setHandler($this->buildCacheHandlerMock());

        return $mock;
    }


    public function testCall()
    {
        $cachedCaller = $this->buildMock();
        $dummy = $this->buildCachedCallerAwareMock();
        $this->callMe = 'not cached';


        // Call without use cache
        $this->isUseCache = false;
        $result = $cachedCaller->call($dummy, 'callMe');
        $this->assertEquals('not cached', $result);


        // Call with force refresh/update
        $this->isUseCache = true;
        $this->isForceRefreshCache = true;

        $cacheHandler = $this->reflectionGet($cachedCaller, 'handler');
        $cacheHandler->set('cacheKey', 'cached');

        $result = $cachedCaller->call($dummy, 'callMe');
        $this->assertEquals('not cached', $result);


        // Call with read from cache
        $this->isUseCache = true;
        $this->isForceRefreshCache = false;

        $cacheHandler->set('cacheKey', 'cached');

        $result = $cachedCaller->call($dummy, 'callMe');
        $this->assertEquals('cached', $result);
    }


    /**
     * Test data is a datetime string, stored in cache with int format.
     */
    public function testCallWithRenderer()
    {
        $cachedCaller = $this->buildMock();
        $cacheHandler = $this->reflectionGet($cachedCaller, 'handler');
        $dummy = $this->buildCachedCallerAwareMock();
        $this->callMe = '2015-01-16 00:50:00';

        $readRenderer = function ($result) {
            return date('Y-m-d H:i:s', $result);
        };
        $writeRenderer = function ($result) {
            return strtotime($result);
        };


        // Write cache
        $this->isUseCache = true;
        $this->isForceRefreshCache = true;
        $result = $cachedCaller->call(
            $dummy,
            'callMe',
            [],
            $readRenderer,
            $writeRenderer
        );
        $this->assertEquals('2015-01-16 00:50:00', $result);
        $this->assertEquals(1421340600, $cacheHandler->get('cacheKey'));


        // Read from cache
        $this->isForceRefreshCache = false;
        $result = $cachedCaller->call(
            $dummy,
            'callMe',
            [],
            $readRenderer,
            $writeRenderer
        );
        $this->assertEquals('2015-01-16 00:50:00', $result);
    }
}
