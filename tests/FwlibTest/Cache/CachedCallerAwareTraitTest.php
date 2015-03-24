<?php
namespace FwlibTest\Cache;

use Fwlib\Cache\CachedCallerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CachedCallerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return  MockObject | CachedCallerAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(CachedCallerAwareTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $model = $this->buildMock();

        $model->setForceRefreshCache(true);
        $this->assertTrue($model->isForceRefreshCache());
        $model->setForceRefreshCache(false);
        $this->assertFalse($model->isForceRefreshCache());

        $model->setUseCache(true);
        $this->assertTrue($model->isUseCache());
        $model->setUseCache(false);
        $this->assertFalse($model->isUseCache());
    }


    public function testGetCacheKey()
    {
        $model = $this->buildMock();

        $key = $model->getCacheKey('callMe', []);
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe', $key);


        $key = $model->getCacheKey('callMe', ['foo', 'bar']);
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/foo/bar', $key);


        $key = $model->getCacheKey('callMe', [['foo', 'bar']]);
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/0/foo/1/bar', $key);


        $key = $model->getCacheKey('callMe', [[['f' => 'b']]]);
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/0/12cf2842', $key);


        $key = $model->getCacheKey('callMe', [new \stdClass]);
        $key = strstr($key, '/', false);
        $this->assertEquals('/callMe/stdClass/99914b93', $key);
    }
}
