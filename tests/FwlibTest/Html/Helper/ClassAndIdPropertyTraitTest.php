<?php
namespace FwlibTest\Html\Helper;

use Fwlib\Html\Helper\ClassAndIdPropertyTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ClassAndIdPropertyTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|ClassAndIdPropertyTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(
            ClassAndIdPropertyTrait::class
        )
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $trait = $this->buildMock();

        $trait->setClass('foo')
            ->setId('bar');
        $this->assertEquals('foo', $this->reflectionCall($trait, 'getClass'));
        $this->assertEquals('bar', $this->reflectionCall($trait, 'getId'));
    }
}
