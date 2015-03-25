<?php
namespace FwlibTest\Web;

use Fwlib\Cache\Handler\PhpArray as PhpArrayCacheHandler;
use Fwlib\Util\Common\Env as EnvUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Web\AbstractViewWithCache;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewWithCacheTest extends PHPUnitTestCase
{
    /**
     * @var EnvUtil
     */
    protected static $envUtilBackup = null;

    /**
     * @var string
     */
    protected static $requestUri = '';


    /**
     * @return MockObject | AbstractViewWithCache
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            AbstractViewWithCache::class,
            ['getCacheLifetime', 'getOutputBody']
        );

        // Long enough lifetime for test
        $mock->expects($this->any())
            ->method('getCacheLifetime')
            ->willReturn(60);

        // Mock un-cached output, remove header and footer, only body part
        // left, and use microtime to simulate output content, because their
        // value are different each time run.
        $this->reflectionSet($mock, 'outputParts', ['body']);
        $mock->expects($this->any())
            ->method('getOutputBody')
            ->will($this->returnCallback(function () {
                $datetimeUtil = UtilContainer::getInstance()->getDatetime();
                return $datetimeUtil->getMicroTime();
            }));

        /** @var AbstractViewWithCache $mock */
        $mock->setCacheHandler(new PhpArrayCacheHandler);

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();
        self::$envUtilBackup = $utilContainer->getEnv();

        $testCase = new self;
        $envUtil = $testCase->getMock(EnvUtil::class, ['getServer']);
        $envUtil->expects($testCase->any())
            ->method('getServer')
            ->willReturnCallback(function() {
                return self::$requestUri;
            });

        $utilContainer->register('Env', $envUtil);
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();
        $utilContainer->register('Env', self::$envUtilBackup);
    }


    public function testForceRefreshCache()
    {
        $view = $this->buildMock();
        $view->setUseCache(true);

        $view->setForceRefreshCache(false);
        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertEquals($x, $y);

        $view->setForceRefreshCache(true);
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();

        $view->setUseCache(false);
        $this->assertFalse($view->isUseCache());

        self::$requestUri = '';

        // Without cache
        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);

        // With cache
        $view->setUseCache(true);
        $this->assertTrue($view->isUseCache());
        $x = $view->getOutput();
        $y = $view->getOutput();
        $this->assertEquals($x, $y);

        // Change cache key, will got different result
        self::$requestUri = 'test.php?a=1&b=';
        $y = $view->getOutput();
        $this->assertNotEquals($x, $y);
    }
}
