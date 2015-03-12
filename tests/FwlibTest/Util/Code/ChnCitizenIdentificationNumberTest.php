<?php
namespace FwlibTest\Util\Code;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\Code\ChnCitizenIdentificationNumber;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ChnCitizenIdentificationNumberTest extends PHPUnitTestCase
{
    /**
     * @return ChnCitizenIdentificationNumber
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getChnCin();
    }


    public function testGenerate()
    {
        $cin = $this->buildMock();

        $x = $cin->generate();
        $this->assertEquals(true, preg_match('/[0-9]{17}[0-9X]/', $x));
    }


    public function testGetBirthday()
    {
        $cin = $this->buildMock();

        $code = '11010519491231002X';
        $this->assertEquals(
            '1949-12-31',
            $cin->getBirthday($code)
        );

        $code = '440524188001010014';
        $this->assertEquals(
            '1880-01-01',
            $cin->getBirthday($code)
        );
    }


    public function testGetGender()
    {
        $cin = $this->buildMock();

        $code = '11010519491231002X';
        $this->assertEquals('女', $cin->getGender($code));

        $code = '440524188001010014';
        $this->assertEquals('男', $cin->getGender($code));
    }


    public function testTo()
    {
        $cin = $this->buildMock();

        $x = '110105491231002';
        $y = '11010519491231002X';
        $this->assertEquals($y, $cin->to18($x));
        $this->assertEquals($x, $cin->to15($y));

        $x = '440524800101001';
        $y = '440524188001010014';
        $this->assertEquals($x, $cin->to15($y));

        // Invalid input string length
        $x = 'foo';
        $this->assertEquals('foo', $cin->to15($x));
        $this->assertEquals('foo', $cin->to18($x));
    }


    public function testValidate()
    {
        $cin = $this->buildMock();

        // Data on @link
        $this->assertEquals(true, $cin->validate('11010519491231002X'));
        $this->assertEquals(true, $cin->validate('440524188001010014'));

        // Fake data
        $this->assertTrue($cin->validate('421029199004091521'));
        $this->assertTrue($cin->validate('820701197812060930'));

        $this->assertEquals(false, $cin->validate('110105194912310024'));
        $this->assertEquals(false, $cin->validate('44052418800101001X'));

        $this->assertFalse($cin->validate('440524800101001'));
    }
}
