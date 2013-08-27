<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Json;

/**
 * Test for Fwlib\Util\Json
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-26
 */
class JsonTest extends PHPunitTestCase
{
    public $dummyForTestEncodeHex = 42;


    public function testDummy()
    {
        $x = array('foo' => 'bar');
        $y = '{"foo":"bar"}';

        $this->assertEquals($y, Json::encode($x));
        $this->assertEqualArray($x, Json::decode($y, true));

    }


    public function testEncodeHex()
    {
        $x = array('<foo>', "'bar'", '"baz"', '&blong&', "\xc3\xa9");

        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","\u00e9"]',
            Json::encodeHex($x, 0)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\'bar\'","\"baz\"","&blong&","\u00e9"]',
            Json::encodeHex($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<foo>","\u0027bar\u0027","\"baz\"","&blong&","\u00e9"]',
            Json::encodeHex($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\u0022baz\u0022","&blong&","\u00e9"]',
            Json::encodeHex($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","\u0026blong\u0026","\u00e9"]',
            Json::encodeHex($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\u0027bar\u0027","\u0022baz\u0022","\u0026blong\u0026","\u00e9"]',
            Json::encodeHex($x)
        );

        $x = array('foo' => 'bar');
        $this->assertEquals(
            '{"foo":"bar"}',
            Json::encodeHex($x)
        );

        $x = new StringUtilTest;
        $this->assertEquals(
            '{"dummyForTestJsonEncodeHex":42}',
            Json::encodeHex($x)
        );
    }


    public function testEncodeUnicode()
    {
        $x = array('<foo>', "'bar'", '"baz"', '&blong&', "\xc3\xa9");

        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","é"]',
            Json::encodeUnicode($x, 0)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\'bar\'","\"baz\"","&blong&","é"]',
            Json::encodeUnicode($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<foo>","\u0027bar\u0027","\"baz\"","&blong&","é"]',
            Json::encodeUnicode($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\u0022baz\u0022","&blong&","é"]',
            Json::encodeUnicode($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","\u0026blong\u0026","é"]',
            Json::encodeUnicode($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","é"]',
            Json::encodeUnicode($x)
        );

        $x = array('foo' => 'é');
        $this->assertEquals(
            '{"foo":"é"}',
            Json::encodeUnicode($x)
        );

		$x = array('中文', array('中' => '文'));
		$y = '["中文",{"中":"文"}]';
		$this->assertEquals($y, Json::encodeUnicode($x));
		$this->assertEqualArray($x, json_decode($y, true));
    }
}
