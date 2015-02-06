<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\StringUtil;
use Fwlib\Util\UtilContainer;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class StringUtilTest extends PHPunitTestCase
{
    /**
     * @return  StringUtil
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getString();
    }


    public function testAddSlashesRecursive()
    {
        $stringUtil = $this->buildMock();

        $x = '';
        $y = $stringUtil->addSlashesRecursive($x);
        $z = '';
        $this->assertEquals($y, $z);

        $x = 'it\'s ok';
        $y = $stringUtil->addSlashesRecursive($x);
        $z = 'it\\\'s ok';
        $this->assertEquals($y, $z);

        $x = array('it\'s ok');
        $y = $stringUtil->addSlashesRecursive($x);
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
        $this->assertEquals($y, $stringUtil->addslashesRecursive($x));

        // Object, should return original
        $x = new StringUtilTest;
        $z = $x;
        $y = $stringUtil->addSlashesRecursive($x);
        $this->assertEquals($y, $z);
    }


    public function testEncodeHtml()
    {
        $stringUtil = $this->buildMock();

        $x = '&';
        $y = '&amp;';
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

        $x = '<>';
        $y = '&lt;&gt;';
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

        $x = '  ';
        $y = '&nbsp; ';
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

        $x = ' ';
        $y = '&nbsp;';
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

        $x = '   ';
        $y = '&nbsp; &nbsp;';
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

        $x = '     ';
        $y = '&nbsp; &nbsp; &nbsp;';
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

        $x = "\r\n";
        $y = "<br />\r\n";
        $this->assertEquals($y, $stringUtil->encodeHtml($x));

    }


    public function testEncodeHtmls()
    {
        $stringUtil = $this->buildMock();

        $x = array('foo' => '&');
        $y = array('foo' => '&amp;');
        $this->assertEquals($y, $stringUtil->encodeHtmls($x));
    }


    public function testIndent()
    {
        $stringUtil = $this->buildMock();

        $x = "  foo\n  bar";
        $y = "    foo\n    bar";
        $this->assertEquals($y, $stringUtil->indent($x, 2));
    }


    public function testIndentHtml()
    {
        $stringUtil = $this->buildMock();

        $x = "  <textarea>
foo
  bar
</textarea>
<hr />";
        $y = "    <textarea>
foo
  bar
</textarea>
  <hr />";
        $this->assertEquals($y, $stringUtil->indentHtml($x, 2));


        // Illegal string without ending tag
        $x = "  <textarea>
foo
  bar
";
        $y = "    <textarea>
foo
  bar
";
        $this->assertEquals($y, $stringUtil->indentHtml($x, 2));
    }


    public function testMatchWildcard()
    {
        $stringUtil = $this->buildMock();

        $this->assertTrue($stringUtil->matchWildcard('duck', '*c?'));
        $this->assertTrue($stringUtil->matchWildcard('duck', '*d???'));
        $this->assertFalse($stringUtil->matchWildcard('duck', '?c*'));

        $s = 'beautiful';
        $this->assertTrue($stringUtil->matchWildcard($s, 'b*f?l'));
        $this->assertTrue($stringUtil->matchWildcard($s, '?e*f*'));
        $this->assertFalse($stringUtil->matchWildcard($s, '?e*f?'));
    }


    public function testMatchRegex()
    {
        $stringUtil = $this->buildMock();

        $x = 'The quick brown fox jumps over the lazy dog';

        $y = $stringUtil->matchRegex('', $x);
        $this->assertEquals(null, $y);

        $y = $stringUtil->matchRegex('/\w{10}/', $x);
        $this->assertEquals(null, $y);

        $y = $stringUtil->matchRegex('/\so\w{3}\s/', $x);
        $this->assertEquals(' over ', $y);

        $y = $stringUtil->matchRegex('/\s(o\w{3})\s/', $x);
        $this->assertEquals('over', $y);

        $y = $stringUtil->matchRegex('/\w{5}/', $x);
        $this->assertEqualArray(array('quick', 'brown', 'jumps'), $y);

        $y = $stringUtil->matchRegex('/(\w{5})/', $x);
        $this->assertEqualArray(array('quick', 'brown', 'jumps'), $y);

        $y = $stringUtil->matchRegex('/((q\w+) (b\w+))/', $x);
        $this->assertEqualArray(array('quick brown', 'quick', 'brown'), $y);
    }


    public function testRandom()
    {
        $stringUtil = $this->buildMock();

        $x = $stringUtil->random(10);
        $this->assertEquals(10, strlen($x));

        $x = $stringUtil->random(10, '0');
        $this->assertEquals('', preg_replace('/[0-9]/', '', $x));

        $x = $stringUtil->random(10, 'a');
        $this->assertEquals('', preg_replace('/[a-z]/', '', $x));

        $x = $stringUtil->random(10, 'A');
        $this->assertEquals('', preg_replace('/[A-Z]/', '', $x));
    }



    public function testSubstrIgnoreHtml()
    {
        $stringUtil = $this->buildMock();

        $x = '测试12&lt;4测试';
        $x = $stringUtil->substrIgnoreHtml($x, 11, '...');
        $y = '测试12&lt;4...';
        $this->assertEquals($y, $x);

        $x = '测<b><i><br / >试</i></b>&quot;<b>234测试</b>';
        $x = $stringUtil->substrIgnoreHtml($x, 9, '...');
        $y = '测<b><i><br / >试</i></b>&quot;<b>2...</b>';
        $this->assertEquals($y, $x);

        $x = '`reStructuredText 中文示例 <?f=20101113-restructuredtext-example.rst>`_';
        $y = $stringUtil->substrIgnoreHtml($x, 71, '');
        $this->assertEquals($y, $x);
    }


    public function testToArray()
    {
        $stringUtil = $this->buildMock();

        $x = ' blah ';
        $y = array('blah');
        $y2 = array(' blah ');
        $this->assertEquals($y, $stringUtil->toArray($x));
        $this->assertEquals($y2, $stringUtil->toArray($x, '|', false));

        $x = 42;
        $y = array('42');
        $this->assertEquals($y, $stringUtil->toArray($x));

        $x = ', a, b, c  , d  , ';
        $this->assertEquals(
            array('a', 'b', 'c', 'd'),
            $stringUtil->toArray($x, ',')
        );
        $this->assertEquals(
            array(',', 'a,', 'b,', 'c', ',', 'd', ','),
            $stringUtil->toArray($x, ' ')
        );
        $this->assertEquals(
            array('', 'a', 'b', 'c',  'd', ''),
            $stringUtil->toArray($x, ',', true, false)
        );
        $this->assertEquals(
            array(' a', ' b', ' c  ',  ' d  ', ' '),
            $stringUtil->toArray($x, ',', false, true)
        );
    }


    public function testToCamelCase()
    {
        $stringUtil = $this->buildMock();

        $x = 'camelCase';
        $y = 'camelCase';
        $this->assertEquals($y, $stringUtil->toCamelCase($x));

        $x = 'CamelCase';
        $this->assertEquals($y, $stringUtil->toCamelCase($x));

        $x = 'camel_case';
        $this->assertEquals($y, $stringUtil->toCamelCase($x));

        $x = 'camel .case';
        $this->assertEquals($y, $stringUtil->toCamelCase($x));
    }


    public function testToSnakeCase()
    {
        $stringUtil = $this->buildMock();

        $x = 'snakeCase';
        $y = 'snake_case';
        $this->assertEquals($y, $stringUtil->toSnakeCase($x));

        $x = 'SnakeCase';
        $this->assertEquals($y, $stringUtil->toSnakeCase($x));

        $x = 'snake_case';
        $this->assertEquals($y, $stringUtil->toSnakeCase($x));

        $x = 'snake .case';
        $this->assertEquals($y, $stringUtil->toSnakeCase($x));

        $y = 'Snake-Case';
        $this->assertEquals($y, $stringUtil->toSnakeCase($x, '-', true));
    }


    public function testToStudlyCaps()
    {
        $stringUtil = $this->buildMock();

        $x = 'studlyCaps';
        $y = 'StudlyCaps';
        $this->assertEquals($y, $stringUtil->toStudlyCaps($x));

        $x = 'StudlyCaps';
        $this->assertEquals($y, $stringUtil->toStudlyCaps($x));

        $x = 'studly_caps';
        $this->assertEquals($y, $stringUtil->toStudlyCaps($x));

        $x = 'studly .caps';
        $this->assertEquals($y, $stringUtil->toStudlyCaps($x));
    }
}
