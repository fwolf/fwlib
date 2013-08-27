<?php
namespace FwlibTest\Mis;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Mis\OrgCode;

/**
 * Test for Fwlib\Mis\OrgCode
 *
 * @package     FwlibTest\Mis
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 */
class OrgCodeTest extends PHPunitTestCase
{
    public function testGen()
    {
        $x = OrgCode::gen('not meet length 8');
        $this->assertEquals('', $x);

        $x = OrgCode::gen('Out  0aA');
        $this->assertEquals('', $x);

        $x = OrgCode::gen();
        $this->assertEquals(true, preg_match('/[0-9A-Z]{8}-[0-9X]/', $x));

        $x = OrgCode::gen('D2143569');
        $this->assertEquals('D2143569-X', $x);

        $x = OrgCode::gen('12345678');
        $this->assertEquals('12345678-8', $x);

        $x = OrgCode::gen('87654321');
        $this->assertEquals('87654321-0', $x);
    }
}
