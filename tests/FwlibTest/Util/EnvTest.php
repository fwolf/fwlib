<?php
namespace FwlibTest\Util;

use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\Env;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class EnvTest extends PHPUnitTestCase
{
    /**
     * @return Env
     */
    public function buildMock()
    {
        return UtilContainer::getInstance()->getEnv();
    }


    public function testEcl()
    {
        $env = $this->buildMock();

        $x = '';
        $y = PHP_EOL;
        $this->assertEquals($y, strip_tags($env->ecl($x, true)));

        $x = ['Foo', 'Bar'];
        $y = "Foo\nBar" . PHP_EOL;
        $this->assertEquals($y, strip_tags($env->ecl($x, true)));

        $x = "  Foo\r\nBar\r\n";
        $y = "  Foo" . PHP_EOL . PHP_EOL . "Bar" . PHP_EOL;
        $this->assertEquals($y, strip_tags($env->ecl($x, true)));
    }
}
