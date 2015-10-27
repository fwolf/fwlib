<?php
namespace FwlibTest\Html\Generator\Component\Form;

use Fwlib\Html\Generator\Component\Form\Form;
use Fwlib\Html\Generator\Component\Form\Validator;
use Fwlib\Html\Generator\Component\Form\ValidatorRenderer;
use Fwlib\Html\Generator\Element\Text;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ValidatorRendererTest extends PHPUnitTestCase
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
            ->setTip('Required')
            ->appendTo($mock);

        return $mock;
    }


    /**
     * @param   string[] $methods
     * @return  MockObject|ValidatorRenderer
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(ValidatorRenderer::class)
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }


    public function testGetFormSelector()
    {
        $form = $this->buildFormMock();
        $form->setClass('bar');

        $renderer = new ValidatorRenderer();
        $renderer->setForm($form);

        $formSelector = $this->reflectionCall($renderer, 'getFormSelector');
        $this->assertEquals('.bar', $formSelector);
    }


    /**
     * @expectedException   \Fwlib\Html\Exception\MissingClassAndIdException
     */
    public function testGetFormSelectorWithNoClassNorId()
    {
        $form = $this->buildFormMock();
        $form->setClass('')->setId('');

        $renderer = new ValidatorRenderer();
        $renderer->setForm($form);

        $this->reflectionCall($renderer, 'getFormSelector');
    }


    public function testGetOutput()
    {
        /** @var MockObject|Validator $validator */
        $validator = $this->getMockBuilder(Validator::class)
            ->setMethods(['getMessages'])
            ->getMock();
        $validator->expects($this->once())
            ->method('getMessages')
            ->willReturn(['foo' => 'Dummy error message']);

        $form = $this->buildFormMock();
        $form->setId('fooForm')
            ->setValidator($validator);

        $renderer = new ValidatorRenderer();
        $renderer->setForm($form);

        $output = $renderer->getOutput();

        $expected = <<<TAG
<script type='text/javascript'>
<!--

(function (global) {

  global.formValidator_fooForm = FormValidator.createNew();

  global.formValidator_fooForm
    .enableCheckOnSubmit()
    .setForm('#fooForm')
    .setRules({"foo":{"title":"","check":["required"],"tip":"Required","checkOnBlur":false,"checkOnKeyup":false}})
    .bind();

  global.formValidator_fooForm.markFailed(global.formValidator_fooForm.getInput('foo'));

}) (window);

-->
</script>
TAG;
        $this->assertEquals($expected, $output);
    }
}
