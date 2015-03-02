<?php
namespace FwlibTest\Util;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\Env;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class EnvTest extends PHPUnitTestCase
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

        $x = ['Foo', 'Bar'];
        $y = "Foo\nBar" . PHP_EOL;
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));

        $x = "  Foo\r\nBar\r\n";
        $y = "  Foo" . PHP_EOL . PHP_EOL . "Bar" . PHP_EOL;
        $this->assertEquals($y, strip_tags($this->env->ecl($x, true)));
    }
}
