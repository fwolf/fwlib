<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\EscapeColor;

/**
 * Test for Fwlib\Util\EscapeColor
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-28
 */
class EscapeColorTest extends PHPunitTestCase
{
    public function testPaint()
    {
        $x = 'foo';
        $this->assertEquals(
            17,
            strlen(EscapeColor::paint($x, 'bright', 'blue', 'yellow'))
        );
        $this->assertEquals(
            17,
            strlen(EscapeColor::paint($x, 1, 34, 43))
        );
        $this->assertEquals(
            3,
            strlen(EscapeColor::paint($x, 'a', 'b', 'c'))
        );

        EscapeColor::$enabled = false;
        $this->assertEquals(
            3,
            strlen(EscapeColor::paint($x, 'bright', 'blue', 'yellow'))
        );
        EscapeColor::$enabled = true;
    }


    public function testPrintTable()
    {
        $x = EscapeColor::printTable(true);
        $this->assertEquals(true, 0 < strlen($x));

        $this->expectOutputRegex('/.+/');
        EscapeColor::printTable();
    }


    public function testToHtml()
    {
        $x = 'foo';
        $y = EscapeColor::paint($x, 'bright', 'blue', 'white');
        $this->assertEquals(
            '<span style="font-weight: bold; color: blue;">foo</span>',
            EscapeColor::toHtml($y)
        );
    }
}
