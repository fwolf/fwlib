<?php
namespace FwlibTest\Html\Generator\Component\Form\Helper;

use Fwlib\Html\Generator\Component\Form\Form;
use Fwlib\Html\Generator\Component\Form\Helper\FormAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FormAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|FormAwareTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(FormAwareTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        /** @var MockObject|Form $form */
        $form = $this->getMockBuilder(Form::class)
            ->getMock();

        $trait = $this->buildMock();

        $trait->setForm($form);
        $this->assertInstanceOf(Form::class, $trait->getForm());
    }
}
