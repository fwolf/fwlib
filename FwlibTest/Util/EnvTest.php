<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Env;

/**
 * Test for Fwlib\Util\Env
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-28
 */
class EnvTest extends PHPunitTestCase
{
    public function testEcl()
    {
        $x = '';
        $y = "\n";
        $this->assertEquals($y, strip_tags(Env::ecl($x, true)));

        $x = array('foo', 'bar');
        $y = "foo\nbar\n";
        $this->assertEquals($y, strip_tags(Env::ecl($x, true)));

        $x = " 	foo\r\nbar\n";
        $y = "foo\n\nbar\n";
        $this->assertEquals($y, strip_tags(Env::ecl($x, true)));
    }
}
