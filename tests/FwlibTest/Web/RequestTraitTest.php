<?php
namespace FwlibTest\Web;

use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Web\RequestTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RequestTraitTest extends PHPUnitTestCase
{
    /**
     * @var string
     */
    protected static $getGet = '';

    /**
     * @var HttpUtil
     */
    protected static $httpUtilBackup = null;


    /**
     * @return MockObject | RequestTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RequestTrait::class)
            ->disableOriginalConstructor()
            ->getMockForTrait();

        /** @noinspection PhpUndefinedFieldInspection */
        {
            $mock->actionParameter = 'a';
            $mock->moduleParameter = 'm';
        }

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();
        self::$httpUtilBackup = $utilContainer->getHttp();

        $testCase = new self;
        $httpUtil = $testCase->getMock(
            HttpUtil::class,
            ['getGet']
        );
        $httpUtil->expects($testCase->any())
            ->method('getGet')
            ->willReturnCallback(function() {
                return RequestTraitTest::$getGet;
            });

        $utilContainer->register('Http', $httpUtil);
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();

        $utilContainer->register('Http', self::$httpUtilBackup);
    }


    public function testGetActionAndModule()
    {
        $request = $this->buildMock();

        self::$getGet = 'foo';
        $this->assertEquals('foo', $request->getAction());

        self::$getGet = 'bar';
        $this->assertEquals('bar', $request->getModule());
    }
}
