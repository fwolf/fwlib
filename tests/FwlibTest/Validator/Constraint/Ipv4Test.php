<?php
namespace FwlibTest\Validator\Constraint;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Ipv4;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Ipv4Test extends PHPUnitTestCase
{
    public function testValidate()
    {
        $constraint = new Ipv4();

        $this->assertTrue($constraint->validate('192.168.0.1'));

        $this->assertFalse($constraint->validate('192.168.01'));
    }
}
