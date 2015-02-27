<?php
namespace FwlibTest\Util\Code;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\Code\ChnOrganizationCode;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ChnOrganizationCodeTest extends PHPUnitTestCase
{
    protected $chnOrgCode;
    protected $utilContainer;


    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->chnOrgCode = new ChnOrganizationCode;
        $this->chnOrgCode->setUtilContainer($this->utilContainer);
    }


    public function testGenerate()
    {
        $x = $this->chnOrgCode->generate('not meet length 8');
        $this->assertEquals('', $x);

        $x = $this->chnOrgCode->generate('Out  0aA');
        $this->assertEquals('', $x);

        $x = $this->chnOrgCode->generate();
        $this->assertEquals(true, preg_match('/[0-9A-Z]{8}-[0-9X]/', $x));

        $x = $this->chnOrgCode->generate('D2143569');
        $this->assertEquals('D2143569-X', $x);

        $x = $this->chnOrgCode->generate('12345678');
        $this->assertEquals('12345678-8', $x);

        $x = $this->chnOrgCode->generate('87654321');
        $this->assertEquals('87654321-0', $x);
    }


    public function testValidate()
    {
        $this->assertEquals(false, $this->chnOrgCode->validate('foo'));
        $this->assertEquals(false, $this->chnOrgCode->validate('foobarblah'));
        $this->assertEquals(false, $this->chnOrgCode->validate('D2143569-1'));

        $this->assertEquals(true, $this->chnOrgCode->validate('D2143569-X'));
        $this->assertEquals(false, $this->chnOrgCode->validate('d2143569-x'));
    }
}
