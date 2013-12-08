<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\ReturnValue;

/**
 * Test for Fwlib\Base\ReturnValue
 *
 * @package     Fwlib\Base\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-05-03
 */
class ReturnValueTest extends PHPunitTestCase
{
    public function testReturnValueDefault()
    {
        $rv = new ReturnValue();

        $this->assertEquals(0, $rv->code());
        $this->assertEquals(null, $rv->message(null, true));
        $this->assertEquals('hi', $rv->message('hi'));
        $this->assertEquals('hi', $rv->message());

        $rv->code(3);
        $this->assertEquals(false, $rv->error());
        $rv->code(-3);
        $this->assertEquals(true, $rv->error());
        $this->assertEquals(-3, $rv->errorCode());
        $this->assertEquals('hi', $rv->errorMessage());

        $rv->data('foobar');
        $this->assertEquals('foobar', $rv->data());

        $this->assertEquals(3, count($rv->getInfo()));
    }
}
