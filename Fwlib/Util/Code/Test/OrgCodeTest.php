<?php
namespace Fwlib\Util\Code\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\Code\OrgCode;

/**
 * Test for Fwlib\Util\Code\OrgCode
 *
 * @package     Fwlib\Util\Code\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 */
class OrgCodeTest extends PHPunitTestCase
{
    protected $orgCode;
    protected $utilContainer;


    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->orgCode = new OrgCode;
        $this->orgCode->setUtilContainer($this->utilContainer);
    }


    public function testGen()
    {
        $x = $this->orgCode->gen('not meet length 8');
        $this->assertEquals('', $x);

        $x = $this->orgCode->gen('Out  0aA');
        $this->assertEquals('', $x);

        $x = $this->orgCode->gen();
        $this->assertEquals(true, preg_match('/[0-9A-Z]{8}-[0-9X]/', $x));

        $x = $this->orgCode->gen('D2143569');
        $this->assertEquals('D2143569-X', $x);

        $x = $this->orgCode->gen('12345678');
        $this->assertEquals('12345678-8', $x);

        $x = $this->orgCode->gen('87654321');
        $this->assertEquals('87654321-0', $x);
    }


    public function testValidate()
    {
        $this->assertEquals(false, $this->orgCode->validate('foo'));
        $this->assertEquals(false, $this->orgCode->validate('foobarblah'));
        $this->assertEquals(false, $this->orgCode->validate('D2143569-1'));

        $this->assertEquals(true, $this->orgCode->validate('D2143569-X'));
        $this->assertEquals(false, $this->orgCode->validate('d2143569-x'));
    }
}
