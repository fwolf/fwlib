<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UuidBase16;

/**
 * Test for Fwlib\Util\UuidBase16
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class UuidBase16Test extends PHPunitTestCase
{
    public function testAddCheckDigit()
    {
        $y = UuidBase16::generateWithSeparator(null, null, true, '-');
        $this->assertEquals($y, UuidBase16::addCheckDigit($y));
    }


    public function testParse()
    {
        // Generate and parse data back
        // '0010' is from default value
        $ar = UuidBase16::parse(UuidBase16::generate());
        $this->assertEquals('0010', $ar['custom1']);

        // Custom field
        $ar = UuidBase16::parse(UuidBase16::generate('1'));
        $this->assertEquals($ar['custom1'], '0001');
        $ar = UuidBase16::parse(UuidBase16::generate('0001', '1312.101'));
        $this->assertEquals($ar['custom2'], '1312.101');

        // Parae data
        $ar = UuidBase16::parse('4822afd9-861b-0000-8302-650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');
        $ar = UuidBase16::parse('4822afd9861b00008302650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');

        $this->assertNull(UuidBase16::parse(null));
    }


    public function testVerify()
    {
        $x = '';
        $this->assertFalse(UuidBase16::verify($x));

        $x = '4822afd9-861b-0000+8302-650a25cda932';
        $this->assertFalse(UuidBase16::verify($x));

        $x = '4822afd9-861b-0000-83026-50a25cda932';
        $this->assertFalse(UuidBase16::verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda93U';
        $this->assertFalse(UuidBase16::verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda932';
        $this->assertFalse(UuidBase16::verify($x, true));

        $x = UuidBase16::generate(null, null, true);
        $this->assertTrue(UuidBase16::verify($x, true));
    }
}