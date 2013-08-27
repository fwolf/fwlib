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

        $x = array(
            "It's 1.",
            "It's 2."   => "It's 3.",
            2012,
            "It's 4."   => array(
                "It's 5."   => array(
                    "It's 6."   => "It's 7.",
                ),
            'end',
            ),
        );
        $y = array(
            "It\\'s 1.",
            "It\\'s 2." => "It\\'s 3.",
            2012,
            "It\\'s 4." => array(
                "It\\'s 5." => array(
                    "It\\'s 6." => "It\\'s 7.",
                ),
            "end",
            ),
        );
        $this->assertEquals($y, StringUtil::addslashesRecursive($x));

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

        $s = 'abcdefg';
        $this->assertEquals(true, StringUtil::matchWildcard($s, 'a*e?g'));
        $this->assertEquals(true, StringUtil::matchWildcard($s, '?b*e*'));
        $this->assertEquals(false, StringUtil::matchWildcard($s, '?b*e?'));
    }


    public function testRandom()
    {
        $x = StringUtil::random(10);
        $this->assertEquals(10, strlen($x));

        $x = StringUtil::random(10, '0');
        $this->assertEquals('', preg_replace('/[0-9]/', '', $x));

        $x = StringUtil::random(10, 'a');
        $this->assertEquals('', preg_replace('/[a-z]/', '', $x));

        $x = StringUtil::random(10, 'A');
        $this->assertEquals('', preg_replace('/[A-Z]/', '', $x));
    }


    public function testToArray()
    {
        $x = ' blah ';
        $y = array('blah');
        $y2 = array(' blah ');
        $this->assertEquals($y, StringUtil::toArray($x));
        $this->assertEquals($y2, StringUtil::toArray($x, '|', false));

        $x = 42;
        $y = array('42');
        $this->assertEquals($y, StringUtil::toArray($x));

        $x = ', a, b, c  , d  , ';
        $this->assertEquals(
            array('a', 'b', 'c', 'd'),
            StringUtil::toArray($x, ',')
        );
        $this->assertEquals(
            array(',', 'a,', 'b,', 'c', ',', 'd', ','),
            StringUtil::toArray($x, ' ')
        );
        $this->assertEquals(
            array('', 'a', 'b', 'c',  'd', ''),
            StringUtil::toArray($x, ',', true, false)
        );
        $this->assertEquals(
            array(' a', ' b', ' c  ',  ' d  ', ' '),
            StringUtil::toArray($x, ',', false, true)
        );
    }


    public function testToCamelCase()
    {
        $x = 'camelCase';
        $y = 'camelCase';
        $this->assertEquals($y, StringUtil::toCamelCase($x));

        $x = 'CamelCase';
        $this->assertEquals($y, StringUtil::toCamelCase($x));

        $x = 'camel_case';
        $this->assertEquals($y, StringUtil::toCamelCase($x));

        $x = 'camel .case';
        $this->assertEquals($y, StringUtil::toCamelCase($x));
    }


    public function testToSnakeCase()
    {
        $x = 'snakeCase';
        $y = 'snake_case';
        $this->assertEquals($y, StringUtil::toSnakeCase($x));

        $x = 'SnakeCase';
        $this->assertEquals($y, StringUtil::toSnakeCase($x));

        $x = 'snake_case';
        $this->assertEquals($y, StringUtil::toSnakeCase($x));

        $x = 'snake .case';
        $this->assertEquals($y, StringUtil::toSnakeCase($x));

        $y = 'Snake-Case';
        $this->assertEquals($y, StringUtil::toSnakeCase($x, '-', true));
    }


    public function testToStudlyCaps()
    {
        $x = 'studlyCaps';
        $y = 'StudlyCaps';
        $this->assertEquals($y, StringUtil::toStudlyCaps($x));

        $x = 'StudlyCaps';
        $this->assertEquals($y, StringUtil::toStudlyCaps($x));

        $x = 'studly_caps';
        $this->assertEquals($y, StringUtil::toStudlyCaps($x));

        $x = 'studly .caps';
        $this->assertEquals($y, StringUtil::toStudlyCaps($x));
    }
}
