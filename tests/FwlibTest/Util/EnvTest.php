<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Env;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
        $y = PHP_EOL;
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));

        $x = array('foo', 'bar');
        $y = "foo\nbar" . PHP_EOL;
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));

        $x = "  foo\r\nbar\r\n";
        $y = "  foo" . PHP_EOL . PHP_EOL . "bar" . PHP_EOL;
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));
    }
}
