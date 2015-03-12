<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\Env;
use Fwlib\Util\UtilContainer;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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


    public function testEclWithNotCli()
    {
        /** @var MockObject|Env $env */
        $env = $this->getMock(Env::class, ['isCli']);
        $env->expects($this->any())
            ->method('isCli')
            ->willReturnOnConsecutiveCalls(true, false);
        // Do not test on array, avoid recursion, keep consecutive order

        $x = 'foo';
        $y = 'foo' . PHP_EOL;
        $this->assertEquals($y, $env->ecl($x, true));

        $y = 'foo<br />' . PHP_EOL;
        $this->assertEquals($y, $env->ecl($x, true));
    }


    public function testEclWithPrint()
    {
        $env = $this->buildMock();

        $env->ecl('foo');
        $this->expectOutputString('foo' . PHP_EOL);
    }


    public function testForceHttps()
    {
        $factory = $this->getFunctionMockFactory(Env::class);
        $headerMock = $factory->get(null, 'header', true);
        $headerMock->setResult([]);

        $env = $this->getMock(
            Env::class,
            ['getServer']
        );
        $env->expects($this->any())
            ->method('getServer')
            ->will($this->onConsecutiveCalls(
                'off',
                'domain.tld',
                '/index.php'
            ));


        /** @var Env $env */
        $env->forceHttps();
        $this->assertEquals(
            'Location: https://domain.tld/index.php',
            $headerMock->getResult()[0]
        );


        $headerMock->disableAll();
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


    /**
     * Most for coverage
     */
    public function testIsMethods()
    {
        $env = $this->buildMock();

        $env->isCli();
        $env->isNixOs();

        $this->assertTrue(true);
    }
}
