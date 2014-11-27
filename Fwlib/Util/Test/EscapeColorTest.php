<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\EscapeColor;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class EscapeColorTest extends PHPunitTestCase
{
    protected $escapeColor;

    public function __construct()
    {
        $this->escapeColor = new EscapeColor;
        $this->escapeColor->setUtilContainer();
    }


    public function testPaint()
    {
        $x = 'foo';
        $this->assertEquals(
            17,
            strlen($this->escapeColor->paint($x, 'bright', 'blue', 'yellow'))
        );
        $this->assertEquals(
            17,
            strlen($this->escapeColor->paint($x, 1, 34, 43))
        );
        $this->assertEquals(
            3,
            strlen($this->escapeColor->paint($x, 'a', 'b', 'c'))
        );

        $this->escapeColor->enabled = false;
        $this->assertEquals(
            3,
            strlen($this->escapeColor->paint($x, 'bright', 'blue', 'yellow'))
        );
        $this->escapeColor->enabled = true;
    }


    public function testPrintTable()
    {
        $x = $this->escapeColor->printTable(true);
        $this->assertEquals(true, 0 < strlen($x));

        $this->expectOutputRegex('/.+/');
        $this->escapeColor->printTable();
    }


    public function testToHtml()
    {
        $x = 'foo';
        $y = $this->escapeColor->paint($x, 'bright', 'blue', 'white');
        $this->assertEquals(
            '<span style="font-weight: bold; color: blue;">foo</span>',
            $this->escapeColor->toHtml($y)
        );
    }
}
