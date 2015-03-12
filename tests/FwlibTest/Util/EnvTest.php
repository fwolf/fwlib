<?php
namespace FwlibTest\Util;

use Fwlib\Util\Env;
use Fwlib\Util\UtilContainer;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class EnvTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @return Env
     */
    protected function buildMock()
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


    public function testGetInput()
    {
        $env = $this->buildMock();

        $factory = $this->getFunctionMockFactory()
            ->setNamespace(Env::class);
        $filterInputMock = $factory->get(null, 'filter_input', true);
        $filterInputArrayMock =
            $factory->get(null, 'filter_input_array', true);


        $filterInputMock->setResult(null);
        $this->assertNull($env->getEnv('dummy', null));
        $this->assertNull($env->getServer('dummy', null));

        $filterInputArrayMock->setResult([]);
        $this->assertEmpty($env->getEnvs());
        $this->assertEmpty($env->getServers());


        $filterInputMock->disableAll();
    }
}
