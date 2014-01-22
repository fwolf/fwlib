<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Ipv4;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-04
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
