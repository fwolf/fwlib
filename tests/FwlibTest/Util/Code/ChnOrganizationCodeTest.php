<?php
namespace FwlibTest\Util\Code;

use Fwlib\Util\UtilContainer;
use Fwlib\Util\Code\ChnOrganizationCode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ChnOrganizationCodeTest extends PHPUnitTestCase
{
    /**
     * @return ChnOrganizationCode
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getChnOrganizationCode();
    }


    public function testGenerate()
    {
        $orgCode = $this->buildMock();

        $x = $orgCode->generate('not meet length 8');
        $this->assertEquals('', $x);

        $x = $orgCode->generate('Out  0aA');
        $this->assertEquals('', $x);

        $x = $orgCode->generate();
        $this->assertEquals(true, preg_match('/[0-9A-Z]{8}-[0-9X]/', $x));

        $x = $orgCode->generate('D2143569');
        $this->assertEquals('D2143569-X', $x);

        $x = $orgCode->generate('12345678');
        $this->assertEquals('12345678-8', $x);

        $x = $orgCode->generate('87654321');
        $this->assertEquals('87654321-0', $x);
    }


    public function testValidate()
    {
        $orgCode = $this->buildMock();

        // Length different
        $this->assertEquals(false, $orgCode->validate('foo'));

        // Same length but has no '-'
        $this->assertEquals(false, $orgCode->validate('fooBarFull'));

        // Algorithm validate
        $this->assertEquals(false, $orgCode->validate('D2143569-1'));

        $this->assertEquals(true, $orgCode->validate('D2143569-X'));
        $this->assertEquals(false, $orgCode->validate('d2143569-x'));
    }
}
