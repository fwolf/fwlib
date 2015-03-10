<?php
namespace FwlibTest\Util;

use Fwlib\Util\HttpUtil;
use Fwlib\Util\UtilContainer;
use FwlibTest\Aide\FunctionMockFactory;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtilTest extends PHPUnitTestCase
{
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
     * Mocked result of native session methods
     *
     * @type string
     */
    public static $sessionId = '';


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

        if (isset($_SESSION)) {
            self::$backups['session'] = $_SESSION;
        }

        self::$backups['cookie'] = $_COOKIE;
    }


    public static function tearDownAfterClass()
    {
        $_GET = self::$backups['get'];
        $_POST = self::$backups['post'];
        $_REQUEST = self::$backups['request'];

        if (isset(self::$backups['session'])) {
            $_SESSION = self::$backups['session'];
        }

        $_COOKIE = self::$backups['cookie'];
    }


    public function testDownload()
    {
        $httpUtil = $this->buildMock();

        $factory = FunctionMockFactory::getInstance();
        $headerMock = $factory->get('Fwlib\Util', 'header', true);

        $x = 'Test for download()';
        $this->expectOutputString($x);
        $httpUtil->download($x);

        $headerMock->disable();
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


    public function testGetGetsAndGetPosts()
    {
        $httpUtil = $this->buildMock();

        $dummy = [
            'foo' => "It's hot",
        ];

        $_GET = $dummy;
        $getParams = $httpUtil->getGets();
        $this->assertEquals("It\\'s hot", $getParams['foo']);

        $_POST = $dummy;
        $getParams = $httpUtil->getPosts();
        $this->assertEquals("It\\'s hot", $getParams['foo']);
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

    }


    public function testGetRequest()
    {
        $httpUtil = $this->buildMock();

        $_REQUEST = [
            'a' => 'foo',
            'b' => ['foo', 'bar'],
        ];

        $this->assertEquals('foo', $httpUtil->getRequest($_REQUEST, 'a'));
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

        $dummy = [
            'a' => '0',
            'b' => '1',
        ];

        $_GET = $dummy;
        $params = $httpUtil->pickGets(['a', 'b'], true);
        $this->assertEqualArray(['b' => '1'], $params);

        $_POST = $dummy;
        $callback = function ($value) {
            return 10 * $value;
        };
        $params = $httpUtil->pickPosts(['a', 'b'], false, $callback);
        $this->assertEqualArray(['a' => 0, 'b' => 10], $params);
    }


    public function testSetGetClearSession()
    {
        $httpUtil = $this->buildMock();

        $httpUtil->setSession('foo', 'bar');
        $this->assertEquals('bar', $httpUtil->getSession('foo'));

        $httpUtil->clearSession();
        $this->assertArrayNotHasKey('foo', $_SESSION);
    }


    /**
     * Please notice that normal cookie set will only be available till next
     * page load, here in test we are using mocked cookie method.
     */
    public function testSetUnsetCookie()
    {
        $httpUtil = $this->buildMock();

        $factory = FunctionMockFactory::getInstance();
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


    public function testStartSession()
    {
        $httpUtil = $this->buildMock();

        $factory = FunctionMockFactory::getInstance();
        $sessionStatusMock =
            $factory->get('Fwlib\Util', 'session_status', true);
        $sessionStartMock =
            $factory->get('Fwlib\Util', 'session_start', true);
        $sessionDestroyMock =
            $factory->get('Fwlib\Util', 'session_destroy', true);


        $sessionStatusMock->setResult(PHP_SESSION_NONE);
        $sessionStartMock->setResult(false);
        $httpUtil->startSession();
        $this->assertTrue($sessionStartMock->getResult());


        $sessionStatusMock->setResult(PHP_SESSION_ACTIVE);
        $sessionStartMock->setResult(false);
        $sessionDestroyMock->setResult(false);
        $httpUtil->startSession(true);
        $this->assertTrue($sessionStartMock->getResult());
        $this->assertTrue($sessionDestroyMock->getResult());

        $sessionStatusMock->disableAll();
    }
}
