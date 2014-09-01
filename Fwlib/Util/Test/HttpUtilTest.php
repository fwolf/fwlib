<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\HttpUtil;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2004-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class HttpUtilTest extends PHPunitTestCase
{
    protected $httpUtil;


    public function __construct()
    {
        $this->httpUtil = new HttpUtil;
        $this->httpUtil->setUtilContainer();
    }


    public function testClearGetSetSession()
    {
        $this->httpUtil->setSession('foo', 'bar');
        $this->assertEquals('bar', $this->httpUtil->getSession('foo'));

        $this->httpUtil->clearSession();
        $this->assertArrayNotHasKey('foo', $_SESSION);
    }


    public function testDownload()
    {
        $x = 'Test for download()';
        $this->expectOutputString($x);
        $this->httpUtil->download($x);
    }


    public function testGetBrowserType()
    {
        $this->assertEquals('gecko', $this->httpUtil->getBrowserType(''));
        $this->assertEquals(null, $this->httpUtil->getBrowserType('none', null));

        // Safari 6.0
        $x = $this->httpUtil->getBrowserType(
            'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)' .
            ' Version/6.0 Mobile/10A5355d Safari/8536.25'
        );
        $this->assertEquals('webkit', $x);

        // IE 10.6
        $x = $this->httpUtil->getBrowserType(
            'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1;' .
            ' .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0'
        );
        $this->assertEquals('trident', $x);

        // Chrome 30.0.1599.17
        $x = $this->httpUtil->getBrowserType(
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)' .
            ' Chrome/30.0.1599.17 Safari/537.36'
        );
        $this->assertEquals('webkit', $x);

    }


    public function testGetParam()
    {
        $_GET = array('a' => 1);
        $x = $this->httpUtil->getUrlParam();
        $y = '?a=1';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1);
        $x = $this->httpUtil->getUrlParam('b', 2);
        $y = '?a=1&b=2';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1, 'b' => '', 'c' => 3);
        $x = $this->httpUtil->getUrlParam(array('a' => 2, 1 => 'a'), array('b', 'c'));
        $y = '?a=2&1=a';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1, 'b' => '', 'c' => 3);
        $x = $this->httpUtil->getUrlParam(array('a' => 2, 1 => 'a'), 'b');
        $y = '?a=2&c=3&1=a';
        $this->assertEquals($y, $x);

    }


    public function testGetRequest()
    {
        $_REQUEST = array(
            'a' => 'foo',
            'b' => array('foo', 'bar'),
        );

        $this->assertEquals('foo', $this->httpUtil->getRequest($_REQUEST, 'a'));
    }


    public function testGetUrlPlan()
    {
        $url = 'http://www.google.com/?a=https://something';
        $this->assertEquals('http', $this->httpUtil->getUrlPlan($url));

        $url = 'https://www.domail.tld/';
        $this->assertEquals('https', $this->httpUtil->getUrlPlan($url));

        $url = 'ftp://domain.tld/';
        $this->assertEquals('ftp', $this->httpUtil->getUrlPlan($url));

        $url = '';
        $this->assertRegExp('/(https?)?/i', $this->httpUtil->getUrlPlan($url));
    }


    public function testStartSession()
    {
        if (0 != session_id()) {
            session_destroy();
        }

        $sessionId = session_id();
        $this->assertEmpty($sessionId);

        $this->httpUtil->startSession();
        $sessionId = session_id();
        $this->assertNotEmpty($sessionId);

        $this->httpUtil->startSession(true);
        $newSessionId = session_id();
        $this->assertNotEquals($sessionId, $newSessionId);
    }
}
