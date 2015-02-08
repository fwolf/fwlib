<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\HttpUtil;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtilTest extends PHPunitTestCase
{
    public static $sessionId = '';
    public static $cookies = array();


    /**
     * @return HttpUtil
     */
    public function buildMock()
    {
        return UtilContainer::getInstance()->getHttp();
    }


    public function testClearGetSetSession()
    {
        $httpUtil = $this->buildMock();

        $httpUtil->setSession('foo', 'bar');
        $this->assertEquals('bar', $httpUtil->getSession('foo'));

        $httpUtil->clearSession();
        $this->assertArrayNotHasKey('foo', $_SESSION);
    }


    public function testDownload()
    {
        $httpUtil = $this->buildMock();

        $x = 'Test for download()';
        $this->expectOutputString($x);
        $httpUtil->download($x);
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


    public function testGetParam()
    {
        $httpUtil = $this->buildMock();

        $_GET = array('a' => 1);
        $x = $httpUtil->getUrlParam();
        $y = '?a=1';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1);
        $x = $httpUtil->getUrlParam('b', 2);
        $y = '?a=1&b=2';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1, 'b' => '', 'c' => 3);
        $x = $httpUtil->getUrlParam(array('a' => 2, 1 => 'a'), array('b', 'c'));
        $y = '?a=2&1=a';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1, 'b' => '', 'c' => 3);
        $x = $httpUtil->getUrlParam(array('a' => 2, 1 => 'a'), 'b');
        $y = '?a=2&c=3&1=a';
        $this->assertEquals($y, $x);

    }


    public function testGetRequest()
    {
        $httpUtil = $this->buildMock();

        $_REQUEST = array(
            'a' => 'foo',
            'b' => array('foo', 'bar'),
        );

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


    /**
     * Please notice that normal cookie set will only be available till next
     * page load, here in test we are using simulated cookie method.
     */
    public function testSetUnsetCookie()
    {
        $httpUtil = $this->getMock(
            'Fwlib\Util\HttpUtil',
            array('getCookie')
        );
        $httpUtil->expects($this->any())
            ->method('getCookie')
            ->will($this->returnCallback(function ($name) {
                $cookies = \FwlibTest\Util\HttpUtilTest::$cookies;

                if (array_key_exists($name, $cookies)) {
                    // Did not check expire time
                    return $cookies[$name];

                } else {
                    return null;
                }
            }));

        /** @type HttpUtil $httpUtil */
        $httpUtil->setCookie('foo', 'bar', time() + 10);
        $this->assertEquals('bar', $httpUtil->getCookie('foo'));

        $httpUtil->setCookie('foo', 'bar', time() - 10);
        $this->assertNull($httpUtil->getCookie('foo'));


        // For unset
        $httpUtil->setCookie('foo', 'bar', time() + 10);
        $this->assertEquals('bar', $httpUtil->getCookie('foo'));

        $httpUtil->unsetCookie('foo');
        $this->assertNull($httpUtil->getCookie('foo'));
    }


    public function testStartSession()
    {
        $httpUtil = $this->buildMock();

        if (0 != $httpUtil->getSessionId()) {
            \Fwlib\Util\session_destroy();
        }

        self::$sessionId = '';
        $sessionId = $httpUtil->getSessionId();
        $this->assertEmpty($sessionId);

        $httpUtil->startSession();
        $sessionId = $httpUtil->getSessionId();
        $this->assertNotEmpty($sessionId);

        $httpUtil->startSession(true);
        $newSessionId = $httpUtil->getSessionId();
        $this->assertNotEquals($sessionId, $newSessionId);
    }
}


namespace Fwlib\Util;

function header($headerString)
{
}


function session_destroy()
{
    \FwlibTest\Util\HttpUtilTest::$sessionId = '';
}


function session_id()
{
    return \FwlibTest\Util\HttpUtilTest::$sessionId;
}


function session_regenerate_id()
{
    \FwlibTest\Util\HttpUtilTest::$sessionId = UtilContainer::getInstance()
        ->getString()
        ->random(10, 'a0');
}


function session_start()
{
    \FwlibTest\Util\HttpUtilTest::$sessionId = UtilContainer::getInstance()
        ->getString()
        ->random(10, 'a0');
}


function setcookie($name, $value, $expire)
{
    if (0 == $expire) {
        $expire = time();
    }

    if (time() > $expire) {
        unset(\FwlibTest\Util\HttpUtilTest::$cookies[$name]);

    } else {
        \FwlibTest\Util\HttpUtilTest::$cookies[$name] = $value;
    }
}
