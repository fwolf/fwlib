<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\CachedCaller;
use Fwlib\Cache\AbstractCachedCallerAware;
use Fwlib\Cache\Handler\PhpArray as PhpArrayCacheHandler;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractCachedCallerAwareTest extends PHPUnitTestCase
{
    /**
     * @return  MockObject | AbstractCachedCallerAware
     */
    protected function buildMock()
    {
        $model = $this->getMock(
            AbstractCachedCallerAware::class,
            ['callMe', 'getCacheLifetime']
        );

        $model->expects($this->once())
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
        $cachedCaller->setHandler(new PhpArrayCacheHandler);

        // The 2nd call will read from cache, callMe() is called only once
        $resultOne = $cachedCaller->call($model, 'callMe', [42]);
        $resultTwo = $cachedCaller->call($model, 'callMe', [42]);

        $this->assertEquals($resultOne, $resultTwo);
    }
}
