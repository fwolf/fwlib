<?php
namespace Fwlib\Util\Code\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Code\ChnCitizenIdentificationNumber;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 */
class ChnCitizenIdentificationNumberTest extends PHPunitTestCase
{
    protected $chnCinCode;
    protected $utilContainer;


    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->chnCinCode = new ChnCitizenIdentificationNumber;
        $this->chnCinCode->setUtilContainer($this->utilContainer);
    }


    public function testGenerate()
    {
        $x = $this->chnCinCode->generate();
        $this->assertEquals(true, preg_match('/[0-9]{17}[0-9X]/', $x));
    }


    public function testGetGender()
    {
        $cin = '11010519491231002X';
        $this->assertEquals('女', $this->chnCinCode->getGender($cin));

        $cin = '440524188001010014';
        $this->assertEquals('男', $this->chnCinCode->getGender($cin));
    }


    public function testTo()
    {
        $x = '110105491231002';
        $y = '11010519491231002X';
        $this->assertEquals($y, $this->chnCinCode->to18($x));
        $this->assertEquals($x, $this->chnCinCode->to15($y));

        $x = '440524800101001';
        $y = '440524188001010014';
        $this->assertEquals($x, $this->chnCinCode->to15($y));

        // Invalid input string length
        $x = 'foo';
        $this->assertEquals('foo', $this->chnCinCode->to15($x));
        $this->assertEquals('foo', $this->chnCinCode->to18($x));
    }


    public function testValidate()
    {
        // Data on @link
        $this->assertEquals(true, $this->chnCinCode->validate('11010519491231002X'));
        $this->assertEquals(true, $this->chnCinCode->validate('440524188001010014'));

        // Fake data
        $this->assertTrue($this->chnCinCode->validate('421029199004091521'));
        $this->assertTrue($this->chnCinCode->validate('820701197812060930'));

        $this->assertEquals(false, $this->chnCinCode->validate('110105194912310024'));
        $this->assertEquals(false, $this->chnCinCode->validate('44052418800101001X'));

        $this->assertFalse($this->chnCinCode->validate('440524800101001'));
    }
}
