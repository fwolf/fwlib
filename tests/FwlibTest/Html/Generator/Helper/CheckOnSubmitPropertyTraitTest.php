<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\CheckOnSubmitPropertyTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CheckOnSubmitPropertyTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|CheckOnSubmitPropertyTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(CheckOnSubmitPropertyTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $trait = $this->buildMock();

        $trait->setCheckOnSubmit(true);
        $this->assertTrue($trait->isCheckOnSubmit());

        $trait->setCheckOnSubmit(false);
        $this->assertFalse($trait->isCheckOnSubmit());
    }
}
