<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Ipv4;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Ipv4Test extends PHPunitTestCase
{
    public function testValidate()
    {
        $constraint = new Ipv4();

        $this->assertTrue($constraint->validate('192.168.0.1'));

        $this->assertFalse($constraint->validate('192.168.01'));
    }
}
