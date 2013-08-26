<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\StringUtil;

/**
 * Test for Fwlib\Util\StringUtil
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2004-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class StringUtilTest extends PHPunitTestCase
{
    public $dummyForTestJsonEncodeHex = 42;


    public function testAddSlashesRecursive()
    {
        $x = '';
        $y = StringUtil::addSlashesRecursive($x);
        $z = '';
        $this->assertEquals($y, $z);

        $x = 'it\'s ok';
        $y = StringUtil::addSlashesRecursive($x);
        $z = 'it\\\'s ok';
        $this->assertEquals($y, $z);

        $x = array('it\'s ok');
        $y = StringUtil::addSlashesRecursive($x);
        $z = array('it\\\'s ok');
        $this->assertEqualArray($y, $z);

        // Object, should return original
        $x = new StringUtilTest;
        $z = $x;
        $y = StringUtil::addSlashesRecursive($x);
        $this->assertEquals($y, $z);
    }


    public function testEncodeHtml()
    {
        $x = '     ';
        $y = '&nbsp; &nbsp; &nbsp;';
        $this->assertEquals($y, StringUtil::encodeHtml($x));
    }


    public function testEvalWithTag()
    {
        $this->assertEquals(null, StringUtil::evalWithTag(''));

        $ar = array('a' => 'string');

        $s = 'substr("{a}", 1, 2)';
        $this->assertEquals('tr', StringUtil::evalWithTag($s, $ar));

        $s = 'substr("[a]", 1, 2)';
        $this->assertEquals('tr', StringUtil::evalWithTag($s, $ar, '[', ']'));

        $s = 'substr("string", 1, 2)';
        $this->assertEquals('tr', StringUtil::evalWithTag($s));

        $s = 'substr("{a}", 1, 2) == "tr"; return false;';
        $this->assertEquals(false, StringUtil::evalWithTag($s));
    }


    public function testMatchWildcard()
    {
        $this->assertEquals(
            true,
            StringUtil::matchWildcard('abcd', '*c?')
        );

        $this->assertEquals(
            false,
            StringUtil::matchWildcard('abcd', '?c*')
        );
    }


    public function testJsonEncodeHex()
    {
        $x = array('<foo>', "'bar'", '"baz"', '&blong&', "\xc3\xa9");

        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","\u00e9"]',
            StringUtil::jsonEncodeHex($x, 0)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\'bar\'","\"baz\"","&blong&","\u00e9"]',
            StringUtil::jsonEncodeHex($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<foo>","\u0027bar\u0027","\"baz\"","&blong&","\u00e9"]',
            StringUtil::jsonEncodeHex($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\u0022baz\u0022","&blong&","\u00e9"]',
            StringUtil::jsonEncodeHex($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","\u0026blong\u0026","\u00e9"]',
            StringUtil::jsonEncodeHex($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\u0027bar\u0027","\u0022baz\u0022","\u0026blong\u0026","\u00e9"]',
            StringUtil::jsonEncodeHex($x)
        );

        $x = array('foo' => 'bar');
        $this->assertEquals(
            '{"foo":"bar"}',
            StringUtil::jsonEncodeHex($x)
        );

        $x = new StringUtilTest;
        $this->assertEquals(
            '{"dummyForTestJsonEncodeHex":42}',
            StringUtil::jsonEncodeHex($x)
        );
    }
}
