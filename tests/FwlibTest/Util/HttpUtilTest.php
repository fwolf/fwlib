<?php
namespace FwlibTest\Util;

use Fwlib\Util\HttpUtil;
use Fwlib\Util\UtilContainer;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtilTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * Backup of globals
     *
     * @type    array[]
     */
    protected static $backups = [];

    /**
     * Mocked result of native cookie methods
     *
     * @type array
     */
    public static $cookies = [];

    /**
     * Mocked result of native header()
     *
     * @type string
     */
    public static $header = '';

    /**
     * @var vfsStreamDirectory
     */
    protected static $vfsRoot = null;


    /**
     * @return HttpUtil
     */
    public function buildMock()
    {
        return UtilContainer::getInstance()->getHttp();
    }


    public static function setUpBeforeClass()
    {
        self::$backups['get'] = $_GET;
        self::$backups['post'] = $_POST;
        self::$backups['request'] = $_REQUEST;
        self::$backups['cookie'] = $_COOKIE;

        self::$vfsRoot = vfsStream::setup('HttpUtilTest');
    }


    public static function tearDownAfterClass()
    {
        $_GET = self::$backups['get'];
        $_POST = self::$backups['post'];
        $_REQUEST = self::$backups['request'];
        $_COOKIE = self::$backups['cookie'];
    }


    public function testDownload()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $headerMock = $factory->get(null, 'header', true);

        $x = 'Test for download()';
        $this->expectOutputString($x);
        $httpUtil->download($x);

        $headerMock->disable();
    }


    public function testDownloadFileAndCheckHeader()
    {
        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(HttpUtil::class, ['getBrowserType']);
        $httpUtil->expects($this->any())
            ->method('getBrowserType')
            ->will($this->returnValue('trident'));

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $headerMock = $factory->get(null, 'header', true);

        $file = vfsStream::newFile('toDownload.txt')->at(self::$vfsRoot);


        // Not assign download file name
        $headerMock->setResult([]);
        $httpUtil->downloadFile($file->url());
        $headers = $headerMock->getResult();
        $headerString = implode(', ', $headers);
        $this->assertRegExp("/filename=\"toDownload\\.txt\"/", $headerString);

        // Assign download file name
        // Filename is fixed for IE
        $headerMock->setResult([]);
        $httpUtil->downloadFile($file->url(), 'foo.bar.txt');
        $headers = $headerMock->getResult();
        $headerString = implode(', ', $headers);
        /** @noinspection SpellCheckingInspection */
        $this->assertRegExp("/filename=\"foo%2ebar\\.txt\"/", $headerString);


        $headerMock->disableAll();
    }


    public function testDownloadFileWithInvalidPath()
    {
        $httpUtil = $this->buildMock();

        $this->assertFalse(
            $httpUtil->downloadFile(__DIR__ . '/not-exist-file')
        );
    }


    public function testFilterInput()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputMock = $factory->get(null, 'filter_input', true);


        $filterInputMock->setResult('bar');
        $y = $httpUtil->filterInput(INPUT_GET, 'dummy', 'foo');
        $this->assertEquals('bar', $y);

        $filterInputMock->setResult(null);
        $y = $httpUtil->filterInput(INPUT_GET, 'dummy', 'foo');
        $this->assertEquals('foo', $y);


        // For coverage
        $filterInputMock->setResult(null);
        $this->assertNull($httpUtil->getCookie('dummy', null));
        $this->assertNull($httpUtil->getGet('dummy', null));
        $this->assertNull($httpUtil->getPost('dummy', null));


        $filterInputMock->disableAll();
    }


    public function testFilterInputArray()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputArrayMock =
            $factory->get(null, 'filter_input_array', false);


        $env = $httpUtil->filterInputArray(INPUT_ENV, FILTER_DEFAULT);
        $this->assertArrayHasKey('PWD', $env);


        // For coverage
        $filterInputArrayMock->enable()->setResult([]);

        $this->assertEmpty($httpUtil->getCookies());
        $this->assertEmpty($httpUtil->getGets());
        $this->assertEmpty($httpUtil->getPosts());


        $filterInputArrayMock->disableAll();
    }


    public function testGetBrowserType()
    {
        $httpUtil = $this->buildMock();

        $this->assertEquals('gecko', $httpUtil->getBrowserType(''));
        $this->assertEquals(null, $httpUtil->getBrowserType('none', null));

        // Safari 6.0
        $x = $httpUtil->getBrowserType(
            'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)' .
            ' Version/6.0 Mobile/10A5355d Safari/8536.25'
        );
        $this->assertEquals('webkit', $x);

        // IE 10.6
        $x = $httpUtil->getBrowserType(
            'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1;' .
            ' .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0'
        );
        $this->assertEquals('trident', $x);

        // Chrome 30.0.1599.17
        $x = $httpUtil->getBrowserType(
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)' .
            ' Chrome/30.0.1599.17 Safari/537.36'
        );
        $this->assertEquals('webkit', $x);

    }


    /**
     * @see http://ideone.com/mO4Fbm    Will only return if key exists
     */
    public function testGetClientIp()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputArrayMock = $factory->get('', 'filter_input_array', true);


        $filterInputArrayMock->setResult([
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $ip = $httpUtil->getClientIp();
        $this->assertEquals('127.0.0.1', $ip);

        $filterInputArrayMock->setResult([]);
        $ip = $httpUtil->getClientIp();
        $this->assertEquals('', $ip);


        $filterInputArrayMock->disableAll();
    }


    public function testGetGetsAndGetPosts()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputArrayMock = $factory->get('', 'filter_input_array', true);

        $dummy = [
            'foo' => "It's hot",
        ];
        $filterInputArrayMock->setResult($dummy);


        // Get Post is not addslashes anymore
        $getParams = $httpUtil->getGets();
        $this->assertEquals("It's hot", $getParams['foo']);


        $getParams = $httpUtil->getPosts();
        $this->assertEquals("It's hot", $getParams['foo']);


        $filterInputArrayMock->disableAll();
    }


    public function testGetParam()
    {
        $httpUtil = $this->buildMock();

        $_GET = ['a' => 1];
        $x = $httpUtil->getUrlParam();
        $y = '?a=1';
        $this->assertEquals($y, $x);

        $_GET = ['a' => 1];
        $x = $httpUtil->getUrlParam('b', 2);
        $y = '?a=1&b=2';
        $this->assertEquals($y, $x);

        $_GET = ['a' => 1, 'b' => '', 'c' => 3];
        $x = $httpUtil->getUrlParam(['a' => 2, 1 => 'a'], ['b', 'c']);
        $y = '?a=2&1=a';
        $this->assertEquals($y, $x);

        $_GET = ['a' => 1, 'b' => '', 'c' => 3];
        $x = $httpUtil->getUrlParam(['a' => 2, 1 => 'a'], 'b');
        $y = '?a=2&c=3&1=a';
        $this->assertEquals($y, $x);

        $_GET = [];
    }


    public function testGetUrlPlan()
    {
        $httpUtil = $this->buildMock();

        $url = 'http://www.google.com/?a=https://something';
        $this->assertEquals('http', $httpUtil->getUrlPlan($url));

        $url = 'https://www.domain.tld/';
        $this->assertEquals('https', $httpUtil->getUrlPlan($url));

        $url = 'ftp://domain.tld/';
        $this->assertEquals('ftp', $httpUtil->getUrlPlan($url));

        $url = '';
        $this->assertRegExp('/(https?)?/i', $httpUtil->getUrlPlan($url));
    }


    public function testPickGetsAndPickPosts()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputArrayMock = $factory->get('', 'filter_input_array', true);

        $dummy = [
            'a' => '0',
            'b' => '1',
        ];
        $filterInputArrayMock->setResult($dummy);


        $params = $httpUtil->pickGets(['a', 'b'], true);
        $this->assertEqualArray(['b' => '1'], $params);


        $callback = function ($value) {
            return 10 * $value;
        };
        $params = $httpUtil->pickPosts(['a', 'b'], false, $callback);
        $this->assertEqualArray(['a' => 0, 'b' => 10], $params);


        $filterInputArrayMock->disableAll();
    }


    /**
     * Please notice that normal cookie set will only be available till next
     * page load, here in test we are using mocked cookie method.
     */
    public function testSetUnsetCookie()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $setcookieMock = $factory->get('Fwlib\Util', 'setcookie', true);


        $httpUtil->setCookie('foo', 'bar', time() + 10);
        $this->assertEquals('bar', $setcookieMock->getResult()['foo']);

        $httpUtil->setCookie('foo', 'bar', time() - 10);
        $this->assertArrayNotHasKey('foo', $setcookieMock->getResult());


        // For unset
        $httpUtil->setCookie('foo', 'bar', time() + 10);
        $this->assertEquals('bar', $setcookieMock->getResult()['foo']);

        $httpUtil->unsetCookie('foo');
        $this->assertArrayNotHasKey('foo', $setcookieMock->getResult());


        $setcookieMock->disable();
    }
}
