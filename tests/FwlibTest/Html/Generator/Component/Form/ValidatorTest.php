<?php
namespace FwlibTest\Html\Generator\Component\Form;

use Fwlib\Html\Generator\Component\Form\Form;
use Fwlib\Html\Generator\Component\Form\Validator;
use Fwlib\Html\Generator\Element\Text;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ValidatorTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Form
     */
    protected function buildFormMock(array $methods = null)
    {
        if (is_null($methods)) {
            $methods = [];
        }
        $methods = array_merge($methods, ['receiveContents']);

        /** @var MockObject|Form $mock */
        $mock = $this->getMock(
            Form::class,
            $methods
        );

        (new Text('foo'))->setValidateRules(['required'])
            ->appendTo($mock);

        (new Text('bar'))->setValidateRules(['regex: /a/'])
            ->setTip('Must have a in value')
            ->appendTo($mock);

        return $mock;
    }


    /**
     * @param   string[] $methods
     * @return  MockObject|Validator
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Validator::class,
            $methods
        );

        return $mock;
    }


    public function testValidate()
    {
        $form = $this->buildFormMock();
        $validator = $this->buildMock()
            ->setForm($form);

        $form['bar']->setValue('hello');
        $this->assertFalse($validator->validate());
        $this->assertEquals(2, count($validator->getMessages()));
    }
}
