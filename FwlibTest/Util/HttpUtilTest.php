<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\HttpUtil;

/**
 * Test for Fwlib\Util\HttpUtil
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2004-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class HttpUtilTest extends PHPunitTestCase
{
    public function testDownload()
    {
        $x = 'Test Fwlib\Util\HttpUtil::download()';
        $this->expectOutputString($x);
        HttpUtil::download($x);
    }


    public function testGetBrowserType()
    {
        $this->assertEquals('gecko', HttpUtil::getBrowserType(''));
        $this->assertEquals(null, HttpUtil::getBrowserType('none', null));

        // Safari 6.0
        $x = HttpUtil::getBrowserType(
            'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)' .
            ' Version/6.0 Mobile/10A5355d Safari/8536.25'
        );
        $this->assertEquals('webkit', $x);

        // IE 10.6
        $x = HttpUtil::getBrowserType(
            'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1;' .
            ' .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0'
        );
        $this->assertEquals('trident', $x);

        // Chrome 30.0.1599.17
        $x = HttpUtil::getBrowserType(
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)' .
            ' Chrome/30.0.1599.17 Safari/537.36'
        );
        $this->assertEquals('webkit', $x);

    }


    public function testGetParam()
    {
        $_GET = array('a' => 1);
        $x = HttpUtil::getUrlParam();
        $y = '?a=1';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1);
        $x = HttpUtil::getUrlParam('b', 2);
        $y = '?a=1&b=2';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1, 'b' => '', 'c' => 3);
        $x = HttpUtil::getUrlParam(array('a' => 2, 1 => 'a'), array('b', 'c'));
        $y = '?a=2&1=a';
        $this->assertEquals($y, $x);

        $_GET = array('a' => 1, 'b' => '', 'c' => 3);
        $x = HttpUtil::getUrlParam(array('a' => 2, 1 => 'a'), 'b');
        $y = '?a=2&c=3&1=a';
        $this->assertEquals($y, $x);

    }


    public function testGetRequest()
    {
        $_REQUEST = array(
            'a' => 'foo',
            'b' => array('foo', 'bar'),
        );

        $this->assertEquals('foo', HttpUtil::getRequest($_REQUEST, 'a'));
    }


    public function testGetUrlPlan()
    {
        $url = 'http://www.google.com/?a=https://something';
        $this->assertEquals('http', HttpUtil::getUrlPlan($url));

        $url = 'https://www.domail.tld/';
        $this->assertEquals('https', HttpUtil::getUrlPlan($url));

        $url = 'ftp://domain.tld/';
        $this->assertEquals('ftp', HttpUtil::getUrlPlan($url));

        $url = '';
        $this->assertRegExp('/(https?)?/i', HttpUtil::getUrlPlan($url));
    }
}
