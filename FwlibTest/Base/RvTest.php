<?php
namespace FwlibTest\Base;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\Rv;

/**
 * Test for Fwlib\Base\Rv
 *
 * @package     FwlibTest\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-05-03
 */
class RvTest extends PHPunitTestCase
{
    public function testRvDefault()
    {
        $rv = new Rv();

        $this->assertEquals(0, $rv->code());
        $this->assertEquals(null, $rv->msg(null, true));
        $this->assertEquals('hi', $rv->msg('hi'));
        $this->assertEquals('hi', $rv->msg());

        $rv->code(3);
        $this->assertEquals(false, $rv->error());
        $rv->code(-3);
        $this->assertEquals(true, $rv->error());
        $this->assertEquals(-3, $rv->errorNo());
        $this->assertEquals('hi', $rv->errorMsg());

        $rv->data('foobar');
        $this->assertEquals('foobar', $rv->data());

        $this->assertEquals(3, count($rv->getInfo()));
    }
}
