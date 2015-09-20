<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\CheckOnBlurPropertyTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CheckOnBlurPropertyTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|CheckOnBlurPropertyTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(CheckOnBlurPropertyTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $trait = $this->buildMock();

        $trait->setCheckOnBlur(true);
        $this->assertTrue($trait->isCheckOnBlur());

        $trait->setCheckOnBlur(false);
        $this->assertFalse($trait->isCheckOnBlur());
    }
}
