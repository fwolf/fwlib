<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Uuid;

/**
 * Test for Fwlib\Util\Uuid
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class UuidTest extends PHPunitTestCase
{
    public function testParse()
    {
        // Generate and parse data back
        $ar = Uuid::parse(Uuid::gen());
        $this->assertEquals($ar['custom1'], '0000');

        // Custom field
        $ar = Uuid::parse(Uuid::gen('1'));
        $this->assertEquals($ar['custom1'], '0001');
        $ar = Uuid::parse(Uuid::gen('0001', '1312.101'));
        $this->assertEquals($ar['custom2'], '1312.101');

        // Parae data
        $ar = Uuid::parse('4822afd9-861b-0000-8302-650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');
    }


    public function testVerify()
    {
        $x = '';
        $this->assertFalse(Uuid::verify($x));

        $x = '4822afd9-861b-0000+8302-650a25cda932';
        $this->assertFalse(Uuid::verify($x));

        $x = '4822afd9-861b-0000-83026-50a25cda932';
        $this->assertFalse(Uuid::verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda93U';
        $this->assertFalse(Uuid::verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda932';
        $this->assertFalse(Uuid::verify($x, true));

        $x = Uuid::gen(null, null, true);
        $this->assertTrue(Uuid::verify($x, true));
    }
}
