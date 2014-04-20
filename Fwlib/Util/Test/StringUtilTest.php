<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\StringUtil;

/**
 * @copyright   Copyright 2004-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class StringUtilTest extends PHPunitTestCase
{
    public $dummyForTestJsonEncodeHex = 42;
    public $stringUtil;


    public function __construct()
    {
        $this->stringUtil = new StringUtil;
    }


    public function testAddSlashesRecursive()
    {
        $x = '';
        $y = $this->stringUtil->addSlashesRecursive($x);
        $z = '';
        $this->assertEquals($y, $z);

        $x = 'it\'s ok';
        $y = $this->stringUtil->addSlashesRecursive($x);
        $z = 'it\\\'s ok';
        $this->assertEquals($y, $z);

        $x = array('it\'s ok');
        $y = $this->stringUtil->addSlashesRecursive($x);
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
        $this->assertEquals($y, $this->stringUtil->addslashesRecursive($x));

        // Object, should return original
        $x = new StringUtilTest;
        $z = $x;
        $y = $this->stringUtil->addSlashesRecursive($x);
        $this->assertEquals($y, $z);
    }


    public function testEncodeHtml()
    {
        $x = '&';
        $y = '&amp;';
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

        $x = '<>';
        $y = '&lt;&gt;';
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

        $x = '  ';
        $y = '&nbsp; ';
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

        $x = ' ';
        $y = '&nbsp;';
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

        $x = '   ';
        $y = '&nbsp; &nbsp;';
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

        $x = '     ';
        $y = '&nbsp; &nbsp; &nbsp;';
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

        $x = "\r\n";
        $y = "<br />\r\n";
        $this->assertEquals($y, $this->stringUtil->encodeHtml($x));

    }


    public function testEvalWithTag()
    {
        $this->assertEquals(null, $this->stringUtil->evalWithTag(''));

        $ar = array('a' => 'string');

        $s = 'substr("{a}", 1, 2)';
        $this->assertEquals('tr', $this->stringUtil->evalWithTag($s, $ar));

        $s = 'substr("[a]", 1, 2)';
        $this->assertEquals('tr', $this->stringUtil->evalWithTag($s, $ar, '[', ']'));

        $s = 'substr("string", 1, 2)';
        $this->assertEquals('tr', $this->stringUtil->evalWithTag($s));

        $s = 'substr("{a}", 1, 2) == "tr"; return false;';
        $this->assertEquals(false, $this->stringUtil->evalWithTag($s));
    }


    public function testMatchWildcard()
    {
        $this->assertEquals(
            true,
            $this->stringUtil->matchWildcard('abcd', '*c?')
        );

        $this->assertEquals(
            false,
            $this->stringUtil->matchWildcard('abcd', '?c*')
        );

        $s = 'abcdefg';
        $this->assertEquals(true, $this->stringUtil->matchWildcard($s, 'a*e?g'));
        $this->assertEquals(true, $this->stringUtil->matchWildcard($s, '?b*e*'));
        $this->assertEquals(false, $this->stringUtil->matchWildcard($s, '?b*e?'));
    }


    public function testMatchRegex()
    {
        $x = 'The quick brown fox jumps over the lazy dog';

        $y = $this->stringUtil->matchRegex('', $x);
        $this->assertEquals(null, $y);

        $y = $this->stringUtil->matchRegex('/\w{10}/', $x);
        $this->assertEquals(null, $y);

        $y = $this->stringUtil->matchRegex('/\so\w{3}\s/', $x);
        $this->assertEquals(' over ', $y);

        $y = $this->stringUtil->matchRegex('/\s(o\w{3})\s/', $x);
        $this->assertEquals('over', $y);

        $y = $this->stringUtil->matchRegex('/\w{5}/', $x);
        $this->assertEqualArray(array('quick', 'brown', 'jumps'), $y);

        $y = $this->stringUtil->matchRegex('/(\w{5})/', $x);
        $this->assertEqualArray(array('quick', 'brown', 'jumps'), $y);

        $y = $this->stringUtil->matchRegex('/((q\w+) (b\w+))/', $x);
        $this->assertEqualArray(array('quick brown', 'quick', 'brown'), $y);
    }


    public function testRandom()
    {
        $x = $this->stringUtil->random(10);
        $this->assertEquals(10, strlen($x));

        $x = $this->stringUtil->random(10, '0');
        $this->assertEquals('', preg_replace('/[0-9]/', '', $x));

        $x = $this->stringUtil->random(10, 'a');
        $this->assertEquals('', preg_replace('/[a-z]/', '', $x));

        $x = $this->stringUtil->random(10, 'A');
        $this->assertEquals('', preg_replace('/[A-Z]/', '', $x));
    }



    public function testSubstrIgnoreHtml()
    {
        $x = '测试12&lt;4测试';
        $x = $this->stringUtil->substrIgnoreHtml($x, 11, '...');
        $y = '测试12&lt;4...';
        $this->assertEquals($y, $x);

        $x = '测<b><i><br / >试</i></b>&quot;<b>234测试</b>';
        $x = $this->stringUtil->substrIgnoreHtml($x, 9, '...');
        $y = '测<b><i><br / >试</i></b>&quot;<b>2...</b>';
        $this->assertEquals($y, $x);

        $x = '`reStructuredText 中文示例 <?f=20101113-restructuredtext-example.rst>`_';
        $y = $this->stringUtil->substrIgnoreHtml($x, 71, '');
        $this->assertEquals($y, $x);
    }


    public function testToArray()
    {
        $x = ' blah ';
        $y = array('blah');
        $y2 = array(' blah ');
        $this->assertEquals($y, $this->stringUtil->toArray($x));
        $this->assertEquals($y2, $this->stringUtil->toArray($x, '|', false));

        $x = 42;
        $y = array('42');
        $this->assertEquals($y, $this->stringUtil->toArray($x));

        $x = ', a, b, c  , d  , ';
        $this->assertEquals(
            array('a', 'b', 'c', 'd'),
            $this->stringUtil->toArray($x, ',')
        );
        $this->assertEquals(
            array(',', 'a,', 'b,', 'c', ',', 'd', ','),
            $this->stringUtil->toArray($x, ' ')
        );
        $this->assertEquals(
            array('', 'a', 'b', 'c',  'd', ''),
            $this->stringUtil->toArray($x, ',', true, false)
        );
        $this->assertEquals(
            array(' a', ' b', ' c  ',  ' d  ', ' '),
            $this->stringUtil->toArray($x, ',', false, true)
        );
    }


    public function testToCamelCase()
    {
        $x = 'camelCase';
        $y = 'camelCase';
        $this->assertEquals($y, $this->stringUtil->toCamelCase($x));

        $x = 'CamelCase';
        $this->assertEquals($y, $this->stringUtil->toCamelCase($x));

        $x = 'camel_case';
        $this->assertEquals($y, $this->stringUtil->toCamelCase($x));

        $x = 'camel .case';
        $this->assertEquals($y, $this->stringUtil->toCamelCase($x));
    }


    public function testToSnakeCase()
    {
        $x = 'snakeCase';
        $y = 'snake_case';
        $this->assertEquals($y, $this->stringUtil->toSnakeCase($x));

        $x = 'SnakeCase';
        $this->assertEquals($y, $this->stringUtil->toSnakeCase($x));

        $x = 'snake_case';
        $this->assertEquals($y, $this->stringUtil->toSnakeCase($x));

        $x = 'snake .case';
        $this->assertEquals($y, $this->stringUtil->toSnakeCase($x));

        $y = 'Snake-Case';
        $this->assertEquals($y, $this->stringUtil->toSnakeCase($x, '-', true));
    }


    public function testToStudlyCaps()
    {
        $x = 'studlyCaps';
        $y = 'StudlyCaps';
        $this->assertEquals($y, $this->stringUtil->toStudlyCaps($x));

        $x = 'StudlyCaps';
        $this->assertEquals($y, $this->stringUtil->toStudlyCaps($x));

        $x = 'studly_caps';
        $this->assertEquals($y, $this->stringUtil->toStudlyCaps($x));

        $x = 'studly .caps';
        $this->assertEquals($y, $this->stringUtil->toStudlyCaps($x));
    }
}
