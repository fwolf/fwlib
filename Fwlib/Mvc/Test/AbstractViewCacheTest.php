<?php
namespace Fwlib\Mvc\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Mvc\AbstractViewCache;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-27
 */
class AbstractViewCacheTest extends PHPunitTestCase
{
    protected $view;
    public static $forceRefreshCache = false;


    protected function buildMock($pathToRoot)
    {
        $view = $this->getMock(
            'Fwlib\Mvc\AbstractViewCache',
            array('getCache', 'getOutputBody', 'newInstanceCache'),
            array($pathToRoot)
        );

        $view->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue(Cache::create()));

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $view->setOutputParts(array('body'));
        $view->expects($this->any())
            ->method('getOutputBody')
            ->will($this->returnCallback(function () {
                $datetimeUtil = UtilContainer::getInstance()->get('DatetimeUtil');
                return $datetimeUtil->getMicroTime();
            }));

        $view->expects($this->any())
            ->method('newInstanceCache')
            ->will($this->returnValue(Cache::create('')));


        return $view;
    }


    protected function buildMockWithForceRefreshCache($pathToRoot)
    {
        $view = $this->getMock(
            'Fwlib\Mvc\AbstractViewCache',
            array('forceRefreshCache', 'getCache', 'getOutputBody'),
            array($pathToRoot)
        );

        $view->expects($this->any())
            ->method('forceRefreshCache')
            ->will($this->returnCallback(function () {
                return AbstractViewCacheTest::$forceRefreshCache;
            }));

        $view->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue(Cache::create('')));

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $view->setOutputParts(array('body'));
        $view->expects($this->any())
            ->method('getOutputBody')
            ->will($this->returnCallback(function () {
                $datetimeUtil = UtilContainer::getInstance()->get('DatetimeUtil');
                return $datetimeUtil->getMicroTime();
            }));


        return $view;
    }


    public function testGetOutput()
    {
        $view = $this->buildMock('path/to/root/');
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
        $view = $this->buildMockWithForceRefreshCache('path/to/root/');
        $view->setUseCache(true);

        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertEquals($x, $y);

        self::$forceRefreshCache = true;
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);
    }
}
