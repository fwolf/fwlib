<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\EscapeColor;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class EscapeColorTest extends PHPUnitTestCase
{
    /**
     * @return EscapeColor
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getEscapeColor();
    }


    public function testPaint()
    {
        $escapeColor = $this->buildMock();

        $x = 'foo';
        $this->assertEquals(
            17,
            strlen($escapeColor->paint($x, 'bright', 'blue', 'yellow'))
        );
        $this->assertEquals(
            17,
            strlen($escapeColor->paint($x, 1, 34, 43))
        );
        $this->assertEquals(
            3,
            strlen($escapeColor->paint($x, 'a', 'b', 'c'))
        );

        $escapeColor->enabled = false;
        $this->assertEquals(
            3,
            strlen($escapeColor->paint($x, 'bright', 'blue', 'yellow'))
        );
        $escapeColor->enabled = true;
    }


    public function testPrintTable()
    {
        $escapeColor = $this->buildMock();

        $x = $escapeColor->printTable(true);
        $this->assertEquals(true, 0 < strlen($x));

        $this->expectOutputRegex('/.+/');
        $escapeColor->printTable();
    }


    public function testToHtml()
    {
        $escapeColor = $this->buildMock();

        $x = 'foo';
        $y = $escapeColor->paint($x, 'bright', 'blue', 'white');
        $this->assertEquals(
            '<span style="font-weight: bold; color: blue;">foo</span>',
            $escapeColor->toHtml($y)
        );
    }
}
