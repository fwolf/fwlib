<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Env;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-28
 */
class EnvTest extends PHPunitTestCase
{
    protected $env;

    public function __construct()
    {
        $this->env = new Env;
    }


    public function testEcl()
    {
        $x = '';
        $y = "\n";
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));

        $x = array('foo', 'bar');
        $y = "foo\nbar\n";
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));

        $x = " 	foo\r\nbar\n";
        $y = "foo\n\nbar\n";
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));
    }
}
