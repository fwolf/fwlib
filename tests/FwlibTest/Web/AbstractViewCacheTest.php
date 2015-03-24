<?php
namespace FwlibTest\Web;

use Fwlib\Cache\Handler\PhpArray as PhpArrayCacheHandler;
use Fwlib\Util\UtilContainer;
use Fwlib\Web\AbstractViewCache;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewCacheTest extends PHPUnitTestCase
{
    protected $view;
    public static $forceRefreshCache = false;


    protected function buildMock()
    {
        $view = $this->getMock(
            AbstractViewCache::class,
            ['getCache', 'getOutputBody', 'newInstanceCache']
        );

        $view->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue(new PhpArrayCacheHandler));

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $this->reflectionSet($view, 'outputParts', ['body']);
        $view->expects($this->any())
            ->method('getOutputBody')
            ->will($this->returnCallback(function () {
                $datetimeUtil = UtilContainer::getInstance()->getDatetime();
                return $datetimeUtil->getMicroTime();
            }));

        $view->expects($this->any())
            ->method('newInstanceCache')
            ->will($this->returnValue(new PhpArrayCacheHandler));


        return $view;
    }


    protected function buildMockWithForceRefreshCache()
    {
        $view = $this->getMock(
            AbstractViewCache::class,
            ['forceRefreshCache', 'getCache', 'getOutputBody']
        );

        $view->expects($this->any())
            ->method('forceRefreshCache')
            ->will($this->returnCallback(function () {
                return AbstractViewCacheTest::$forceRefreshCache;
            }));

        $view->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue(new PhpArrayCacheHandler));

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $this->reflectionSet($view, 'outputParts', ['body']);
        $view->expects($this->any())
            ->method('getOutputBody')
            ->will($this->returnCallback(function () {
                $datetimeUtil = UtilContainer::getInstance()->getDatetime();
                return $datetimeUtil->getMicroTime();
            }));


        return $view;
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();
        $view->setUseCache(false);
        $this->assertFalse($view->getUseCache());

        // Force use $_SERVER['argv'] for cache key
        unset($_SERVER['REQUEST_URI']);

        // Without cache
        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);

        // With cache
        $view->setUseCache(true);
        $this->assertTrue($view->getUseCache());
        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertEquals($x, $y);

        // Change cache key, will got different result
        $_SERVER['REQUEST_URI'] = 'test.php?a=1&b=';
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);
    }


    public function testForceRefreshCache()
    {
        $view = $this->buildMockWithForceRefreshCache();
        $view->setUseCache(true);

        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertEquals($x, $y);

        self::$forceRefreshCache = true;
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);
    }
}
