<?php
namespace FwlibTest\Config;

use Fwlib\Config\StringOptions;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class StringOptionsTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|StringOptions
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            StringOptions::class,
            $methods
        );

        return $mock;
    }


    public function testExport()
    {
        $options = $this->buildMock();

        $options->set('foo1', 42)
            ->set('foo2', true)
            ->set('foo3', false);
        $this->assertEquals(
            'foo1: 42, foo2: true, foo3: false',
            $options->export(', ', ': ')
        );
    }


    public function testImport()
    {
        $options = $this->buildMock();

        $options->import(',,foo1 = 4 2 , foo2=true  , foo3 = false', ',', '=');

        $this->assertEquals('4 2', $options->get('foo1'));
        $this->assertTrue($options->get('foo2'));
        $this->assertFalse($options->get('foo3'));
    }


    /**
     * @expectedException   \Fwlib\Base\Exception\InvalidFormatException
     */
    public function testImportError()
    {
        $options = $this->buildMock();

        $options->import('foo == 42', ',', '=');
    }


    public function testOtherSeparatorAndKvSplitter()
    {
        /** @var MockObject|StringOptions $options */
        $options = $this->getMock(
            StringOptions::class,
            null,
            ['foo:: 42; bar', ';', '::']
        );

        $this->assertEquals(42, $options->get('foo'));
        $this->assertTrue($options->get('bar'));
    }
}
