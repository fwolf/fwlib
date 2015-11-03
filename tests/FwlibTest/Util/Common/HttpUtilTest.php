<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\Env;
use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use FwlibTest\Aide\FunctionMockAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtilTest extends PHPUnitTestCase
{
    use FunctionMockAwareTrait;


    /**
     * @var Env
     */
    protected static $envUtilBackup = null;

    /**
     * @var vfsStreamDirectory
     */
    protected static $vfsRoot = null;


    /**
     * @return HttpUtil
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getHttp();
    }


    public static function setUpBeforeClass()
    {
        self::$vfsRoot = vfsStream::setup('HttpUtilTest');

        $utilContainer = UtilContainer::getInstance();
        self::$envUtilBackup = $utilContainer->getEnv();
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();
        $utilContainer->register('Env', self::$envUtilBackup);
    }


    public function testDownload()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $headerMock = $factory->get(null, 'header', true);

        $expected = 'Test for download()';
        $this->expectOutputString($expected);
        $httpUtil->download($expected);

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


    public function testGetBrowserType()
    {
        $httpUtil = $this->buildMock();

        $envUtil = $this->getMock(Env::class, ['getServer']);
        $envUtil->expects($this->any())
            ->method('getServer')
            ->willReturnOnConsecutiveCalls('', 'foo bar');
        UtilContainer::getInstance()->register('Env', $envUtil);


        $this->assertEquals('', $httpUtil->getBrowserType(null));
        $this->assertEquals('', $httpUtil->getBrowserType('foo bar'));

        // Not use consecutive return value anymore
        $this->assertEquals('', $httpUtil->getBrowserType(''));

        // Safari 6.0
        $browserType = $httpUtil->getBrowserType(
            'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)' .
            ' Version/6.0 Mobile/10A5355d Safari/8536.25'
        );
        $this->assertEquals('webkit', $browserType);

        // IE 10.6
        $browserType = $httpUtil->getBrowserType(
            'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1;' .
            ' .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0'
        );
        $this->assertEquals('trident', $browserType);

        // Chrome 30.0.1599.17
        $browserType = $httpUtil->getBrowserType(
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)' .
            ' Chrome/30.0.1599.17 Safari/537.36'
        );
        $this->assertEquals('webkit', $browserType);
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
        $ipStr = $httpUtil->getClientIp();
        $this->assertEquals('127.0.0.1', $ipStr);

        $filterInputArrayMock->setResult([]);
        $ipStr = $httpUtil->getClientIp();
        $this->assertEquals('', $ipStr);


        $filterInputArrayMock->disableAll();
    }


    public function testGetInput()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputMock = $factory->get(null, 'filter_input', true);


        $filterInputMock->setResult(null);
        $this->assertNull($httpUtil->getCookie('dummy', null));
        $this->assertNull($httpUtil->getGet('dummy', null));
        $this->assertNull($httpUtil->getPost('dummy', null));


        $filterInputMock->disableAll();
    }


    public function testGetInputs()
    {
        $httpUtil = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(HttpUtil::class);
        $filterInputArrayMock =
            $factory->get(null, 'filter_input_array', true);


        $filterInputArrayMock->setResult([]);
        $this->assertEmpty($httpUtil->getCookies());
        $this->assertEmpty($httpUtil->getGets());
        $this->assertEmpty($httpUtil->getPosts());


        $filterInputArrayMock->disableAll();
    }


    public function testGetSelfHostUrl()
    {
        $envUtil = $this->getMock(Env::class, ['getServer']);
        $envUtil->expects($this->any())
            ->method('getServer')
            ->willReturnOnConsecutiveCalls('', 'domain.tld');
        UtilContainer::getInstance()->register('Env', $envUtil);

        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(HttpUtil::class, ['isHttps']);
        $httpUtil->expects($this->any())
            ->method('isHttps')
            ->willReturn(true);


        // No host
        $this->assertEquals('', $httpUtil->getSelfHostUrl());

        $this->assertEquals(
            'https://domain.tld',
            $httpUtil->getSelfHostUrl()
        );
    }


    public function testGetSelfUrl()
    {
        $selfHostUrl = 'http://domain.tld';
        $requestUri = '/foo.php?p=42';
        $urlWithoutQuery = 'http://domain.tld/bar.php';

        $envUtil = $this->getMock(Env::class, ['getServer']);
        $envUtil->expects($this->any())
            ->method('getServer')
            ->willReturn($requestUri);
        UtilContainer::getInstance()->register('Env', $envUtil);

        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(
            HttpUtil::class,
            ['getSelfHostUrl', 'getSelfUrlWithoutQueryString']
        );
        $httpUtil->expects($this->any())
            ->method('getSelfHostUrl')
            ->willReturn($selfHostUrl);
        $httpUtil->expects($this->any())
            ->method('getSelfUrlWithoutQueryString')
            ->willReturn($urlWithoutQuery);


        $this->assertEquals(
            $selfHostUrl . $requestUri,
            $httpUtil->getSelfUrl(true)
        );
        $this->assertEquals(
            $urlWithoutQuery,
            $httpUtil->getSelfUrl(false)
        );
    }


    public function testGetSelfUrlWithoutQueryString()
    {
        $selfHostUrl = 'http://domain.tld';
        $scriptName = '/foo.php';

        $envUtil = $this->getMock(Env::class, ['getServer']);
        $envUtil->expects($this->any())
            ->method('getServer')
            ->willReturn($scriptName);
        UtilContainer::getInstance()->register('Env', $envUtil);

        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(
            HttpUtil::class,
            ['getSelfHostUrl']
        );
        $httpUtil->expects($this->any())
            ->method('getSelfHostUrl')
            ->willReturnOnConsecutiveCalls('', $selfHostUrl);


        $this->assertEquals(
            '',
            $httpUtil->getSelfUrlWithoutQueryString()
        );
        $this->assertEquals(
            $selfHostUrl . $scriptName,
            $httpUtil->getSelfUrlWithoutQueryString()
        );
    }


    public function testGetUrlParam()
    {
        $selfUrl = 'http://domain.tld/foo.php';

        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(
            HttpUtil::class,
            ['getGets', 'getSelfUrlWithoutQueryString']
        );
        $httpUtil->expects($this->any())
            ->method('getGets')
            ->willReturnOnConsecutiveCalls(
                ['a' => 1],
                ['a' => 1],
                ['a' => 1, 'b' => '', 'c' => 3],
                ['a' => 1, 'b' => '', 'c' => 3]
            );
        $httpUtil->expects($this->once())
            ->method('getSelfUrlWithoutQueryString')
            ->willReturn($selfUrl);


        /** @noinspection PhpDeprecationInspection */
        {
            $qStr = $httpUtil->getUrlParam();
            $this->assertEquals('?a=1', $qStr);

            $qStr = $httpUtil->getUrlParam('b', 2);
            $this->assertEquals('?a=1&b=2', $qStr);

            $qStr = $httpUtil->getUrlParam(['a' => 2, 1 => 'a'], ['b', 'c']);
            $this->assertEquals('?a=2&1=a', $qStr);

            $qStr = $httpUtil->getUrlParam(['a' => 2, 1 => 'a'], 'b', true);
            $this->assertEquals($selfUrl . '?a=2&c=3&1=a', $qStr);
        }
    }


    public function testGetUrlPlan()
    {
        /** @var MockObject|HttpUtil $httpUtil */
        $httpUtil = $this->getMock(
            HttpUtil::class,
            ['getSelfHostUrl']
        );
        $httpUtil->expects($this->any())
            ->method('getSelfUrl')
            ->willReturn('https://domain.tld/foo.php?bar=42');


        $url = 'http://domain.tld/?a=https://something';
        $this->assertEquals('http', $httpUtil->getUrlPlan($url));

        $url = 'HTTP://domain.tld/';
        $this->assertEquals('http', $httpUtil->getUrlPlan($url));

        $url = 'ftp://domain.tld/';
        $this->assertEquals('ftp', $httpUtil->getUrlPlan($url));

        $url = '';
        $this->assertRegExp('/(https?)?/i', $httpUtil->getUrlPlan($url));
    }


    public function testIsHttps()
    {
        $envUtil = $this->getMock(
            Env::class,
            ['getServer']
        );
        $envUtil->expects($this->any())
            ->method('getServer')
            ->willReturnOnConsecutiveCalls(null, 'off', 'on');

        $utilContainer = UtilContainer::getInstance();
        $utilContainer->register('Env', $envUtil);

        $httpUtil = $this->buildMock();

        $this->assertFalse($httpUtil->isHttps());
        $this->assertFalse($httpUtil->isHttps());
        $this->assertTrue($httpUtil->isHttps());
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

        $factory = $this->getFunctionMockFactory(HttpUtil::class);
        $setcookieMock = $factory->get(null, 'setcookie', true);


        $httpUtil->setCookie('foo', 'bar', time() + 10);
        $this->assertEquals('bar', $setcookieMock->getResult()['foo']);

        $httpUtil->setCookie('foo', 'bar', time() - 10, '/path/');
        $this->assertArrayNotHasKey('foo', $setcookieMock->getResult());


        // For unset
        $httpUtil->setCookie('foo', 'bar', time() + 10, '/path', 'domain.tld');
        $this->assertEquals('bar', $setcookieMock->getResult()['foo']);

        $httpUtil->unsetCookie('foo');
        $this->assertArrayNotHasKey('foo', $setcookieMock->getResult());


        $setcookieMock->disable();
    }
}
