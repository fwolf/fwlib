<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\ListView;
use Fwlib\Html\ListView\Request;
use Fwlib\Html\ListView\RequestSource;
use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RequestTest extends PHPUnitTestCase
{
    /**
     * @var HttpUtil
     */
    protected static $httpUtilBackup;

    /**
     * @var string
     */
    protected static $selfUrl = 'http://domain.tld/list.php?foo=bar';


    /**
     * @return MockObject | Request
     */
    protected function buildMock()
    {
        $mock = $this->getMock(Request::class, null);

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();
        self::$httpUtilBackup = $utilContainer->getHttp();

        $testCase = new self;
        $httpUtil = $testCase->getMock(
            HttpUtil::class,
            ['getSelfUrl', 'getGet', 'getPost']
        );

        $httpUtil->expects($testCase->any())
            ->method('getSelfUrl')
            ->willReturn(self::$selfUrl);

        $httpUtil->expects($testCase->any())
            ->method('getGet')
            ->willReturn('get');

        $httpUtil->expects($testCase->any())
            ->method('getPost')
            ->willReturn('post');

        $utilContainer->register('Http', $httpUtil);
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();
        $utilContainer->register('Http', self::$httpUtilBackup);
    }


    public function testGetBaseUrl()
    {
        $request = $this->buildMock();

        $this->assertEquals(self::$selfUrl, $request->getBaseUrl());

        $request->setBaseUrl('dummy url');
        $this->assertEquals('dummy url', $request->getBaseUrl());
    }


    public function testGetOrderBy()
    {
        /** @var MockObject|Request $request */
        $request = $this->getMock(Request::class, ['getRequest']);

        $request->expects($this->any())
            ->method('getRequest')
            ->willReturnOnConsecutiveCalls(null, 'foo', 'desc');

        $this->assertEqualArray([], $request->getOrderBy());

        $this->assertEqualArray(['foo' => 'DESC'], $request->getOrderBy());
    }


    public function testGetPage()
    {
        /** @var MockObject|Request $request */
        $request = $this->getMock(Request::class, ['getRequest']);

        $request->expects($this->any())
            ->method('getRequest')
            ->willReturnOnConsecutiveCalls('42', '', '-1');

        $this->assertEquals(42, $request->getPage());
        $this->assertEquals(1, $request->getPage());
        $this->assertEquals(1, $request->getPage());
    }


    public function testGetPageSize()
    {
        /** @var MockObject|Request $request */
        $request = $this->getMock(Request::class, ['getRequest']);

        $request->expects($this->any())
            ->method('getRequest')
            ->willReturnOnConsecutiveCalls('20', ListView::PAGE_SIZE_NOT_SET);

        $this->assertEquals(20, $request->getPageSize());
        $this->assertEquals(
            ListView::PAGE_SIZE_NOT_SET,
            $request->getPageSize()
        );
    }


    public function testGetRequest()
    {
        $request = $this->buildMock();

        $request->setRequestSource(RequestSource::GET);
        $this->assertEquals(
            'get',
            $this->reflectionCall($request, 'getRequest', ['dummy'])
        );

        $request->setRequestSource(RequestSource::POST);
        $this->assertEquals(
            'post',
            $this->reflectionCall($request, 'getRequest', ['dummy'])
        );
    }


    /**
     * @expectedException \Fwlib\Html\ListView\Exception\InvalidRequestSourceException
     */
    public function testGetRequestWithInvalidSource()
    {
        $request = $this->buildMock();

        $request->setRequestSource('invalid source');
        $this->reflectionCall($request, 'getRequest', ['dummy']);
    }
}
