<?php
namespace Fwlib\Mvc\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Cache\Cache;
use Fwlib\Mvc\AbstractViewCache;
use Fwlib\Test\ServiceContainerTest;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-27
 */
class AbstractViewCacheTest extends PHPunitTestCase
{
    protected $serviceContainer;
    protected $view;
    public static $forceRefreshCache = false;


    public function __construct()
    {
        $this->serviceContainer = ServiceContainerTest::getInstance();
    }


    protected function buildMock($pathToRoot)
    {
        $view = $this->getMock(
            'Fwlib\Mvc\AbstractViewCache',
            array('getOutputBody', 'newInstanceCache'),
            array($pathToRoot)
        );

        $view->setServiceContainer($this->serviceContainer);
        $this->serviceContainer->register('Cache', Cache::create());

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $view->setOutputPart(array('body'));
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
            array('forceRefreshCache', 'getOutputBody', 'newInstanceCache'),
            array($pathToRoot)
        );

        $view->setServiceContainer($this->serviceContainer);

        $view->expects($this->any())
            ->method('forceRefreshCache')
            ->will($this->returnCallback(function () {
                return AbstractViewCacheTest::$forceRefreshCache;
            }));

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $view->setOutputPart(array('body'));
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


    public function testGetOutput()
    {
        $view = $this->buildMock('path/to/root/');
        $view->setUseCache(false);
        $this->assertFalse($view->getUseCache());

        // Force use $_SERVER['argv'] for cache key
        unset($_SERVER['REQUEST_URI']);

        // Without cache
        $x = $view->getOutput(null);
        $y = $view->getOutput(null);
        $this->assertNotEquals($x, $y);

        // With cache
        $view->setUseCache(true);
        $this->assertTrue($view->getUseCache());
        $x = $view->getOutput(null);
        $y = $view->getOutput(null);
        $this->assertEquals($x, $y);

        // Change cache key, will got different result
        $_SERVER['REQUEST_URI'] = 'test.php?a=1&b=';
        $y = $view->getOutput(null);
        $this->assertNotEquals($x, $y);
    }


    public function testForceRefreshCache()
    {
        $view = $this->buildMockWithForceRefreshCache('path/to/root/');
        $view->setUseCache(true);

        $x = $view->getOutput(null);
        $y = $view->getOutput(null);
        $this->assertEquals($x, $y);

        self::$forceRefreshCache = true;
        $y = $view->getOutput(null);
        $this->assertNotEquals($x, $y);
    }
}
