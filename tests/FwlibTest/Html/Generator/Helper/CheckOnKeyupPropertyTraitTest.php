<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CheckOnKeyupPropertyTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|CheckOnKeyupPropertyTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(CheckOnKeyupPropertyTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $trait = $this->buildMock();

        $trait->setCheckOnKeyup(true);
        $this->assertTrue($trait->isCheckOnKeyup());

        $trait->setCheckOnKeyup(false);
        $this->assertFalse($trait->isCheckOnKeyup());
    }
}
