<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UuidBase16;

/**
 * @copyright   Copyright 2008-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class UuidBase16Test extends PHPunitTestCase
{
    protected $uuid;
    protected $uuidContainer;


    public function __construct()
    {
        $utilContainer = UtilContainer::getInstance();
        $this->uuid = new UuidBase16;
        $this->uuid->setUtilContainer($utilContainer);
    }


    public function testAddCheckDigit()
    {
        $y = $this->uuid->generateWithSeparator(null, null, true, '-');
        $this->assertEquals($y, $this->uuid->addCheckDigit($y));
    }


    public function testParse()
    {
        // Generate and parse data back
        // '0010' is from default value
        $ar = $this->uuid->parse($this->uuid->generate());
        $this->assertEquals('0010', $ar['custom1']);

        // Custom field
        $ar = $this->uuid->parse($this->uuid->generate('1'));
        $this->assertEquals($ar['custom1'], '0001');
        $ar = $this->uuid->parse($this->uuid->generate('0001', '1312.101'));
        $this->assertEquals($ar['custom2'], '1312.101');

        // Parae data
        $ar = $this->uuid->parse('4822afd9-861b-0000-8302-650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');
        $ar = $this->uuid->parse('4822afd9861b00008302650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');

        $this->assertNull($this->uuid->parse(null));
    }


    public function testVerify()
    {
        $x = '';
        $this->assertFalse($this->uuid->verify($x));

        $x = '4822afd9-861b-0000+8302-650a25cda932';
        $this->assertFalse($this->uuid->verify($x));

        $x = '4822afd9-861b-0000-83026-50a25cda932';
        $this->assertFalse($this->uuid->verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda93U';
        $this->assertFalse($this->uuid->verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda932';
        $this->assertFalse($this->uuid->verify($x, true));

        $x = $this->uuid->generate(null, null, true);
        $this->assertTrue($this->uuid->verify($x, true));
    }
}
