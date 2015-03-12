<?php
namespace FwlibTest\Util\Uuid;

use Fwlib\Util\UtilContainer;
use Fwlib\Util\Uuid\Base16;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base16Test extends PHPUnitTestCase
{
    /**
     * @return Base16
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getUuidBase16();
    }


    public function testAddCheckDigit()
    {
        $uuidGenerator = $this->buildMock();

        $y = $uuidGenerator->generateWithSeparator(null, null, true, '-');
        $this->assertEquals($y, $uuidGenerator->addCheckDigit($y));
    }


    public function testParse()
    {
        $uuidGenerator = $this->buildMock();

        // Generate and parse data back
        // '0010' is from default value
        $ar = $uuidGenerator->parse($uuidGenerator->generate());
        $this->assertEquals('0010', $ar['custom1']);

        // Custom field
        $ar = $uuidGenerator->parse($uuidGenerator->generate('1'));
        $this->assertEquals($ar['custom1'], '0001');
        $ar = $uuidGenerator->parse(
            $uuidGenerator->generate('0001', '1312.101')
        );
        $this->assertEquals($ar['custom2'], '1312.101');

        // Parse data
        $ar = $uuidGenerator->parse('4822afd9-861b-0000-8302-650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');
        $ar = $uuidGenerator->parse('4822afd9861b00008302650a25cda932');
        $this->assertEquals($ar['timeLow'], 1210232793);
        $this->assertEquals($ar['timeMid'], 34331);
        $this->assertEquals($ar['custom1'], '0000');
        $this->assertEquals($ar['custom2'], '8302650a');
        $this->assertEquals($ar['ip'], '131.2.101.10');

        $this->assertNull($uuidGenerator->parse(null));
    }


    public function testVerify()
    {
        $uuidGenerator = $this->buildMock();

        $x = '';
        $this->assertFalse($uuidGenerator->verify($x));

        $x = '4822afd9-861b-0000+8302-650a25cda932';
        $this->assertFalse($uuidGenerator->verify($x));

        $x = '4822afd9-861b-0000-83026-50a25cda932';
        $this->assertFalse($uuidGenerator->verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda93U';
        $this->assertFalse($uuidGenerator->verify($x));

        $x = '4822afd9-861b-0000-8302-650a25cda932';
        $this->assertFalse($uuidGenerator->verify($x, true));

        $x = $uuidGenerator->generate(null, null, true);
        $this->assertTrue($uuidGenerator->verify($x, true));
    }
}
