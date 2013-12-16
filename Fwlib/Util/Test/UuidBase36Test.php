<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UuidBase36;

/**
 * Test for Fwlib\Util\UuidBase36
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-04
 */
class UuidBase36Test extends PHPunitTestCase
{
    public function testAddCheckDigit()
    {
        $y = UuidBase36::generate(null, null, true);
        $this->assertEquals($y, UuidBase36::addCheckDigit($y));
    }


    public function testParse()
    {
        // Group
        $ar = UuidBase36::parse(UuidBase36::generate());
        $this->assertEquals('10', $ar['group']);
        $ar = UuidBase36::parse(UuidBase36::generate('1'));
        $this->assertEquals('01', $ar['group']);

        // Custom
        $ar = UuidBase36::parse(UuidBase36::generate('', '000'));
        $this->assertEquals('000', substr($ar['custom'], -3));

        // Parae data
        $ar = UuidBase36::parse('mvqtti07x4a01a93alw6tz9qp');
        $this->assertEquals(1383575670, $ar['second']);
        $this->assertEquals(10264, $ar['microsecond']);
        $this->assertEquals('a0', $ar['group']);
        $this->assertEquals('1a93alw', $ar['custom']);
        $this->assertEquals('166.178.121.116', $ar['ip']);

        $this->assertNull(UuidBase36::parse(null));
    }


    public function testVerify()
    {
        $x = '';
        $this->assertFalse(UuidBase36::verify($x));

        $x = 'mvqwzsaypm00sa2t8f0i9ooky';
        $this->assertTrue(UuidBase36::verify($x, true));

        $x = 'mvqwzsaypm00sa2t8f0i9ook+';
        $this->assertFalse(UuidBase36::verify($x));

        $x = 'mvqwzsaypm00sa2t8f0i9ookx';
        $this->assertFalse(UuidBase36::verify($x, true));

        $x = UuidBase36::generate(null, null, true);
        $this->assertTrue(UuidBase36::verify($x, true));
    }
}
