<?php
namespace FwlibTest\Html\Generator\Component\Form;

use Fwlib\Html\Generator\Component\Form\Form;
use Fwlib\Html\Generator\Component\Form\Renderer;
use Fwlib\Html\Generator\Element\Hidden;
use Fwlib\Html\Generator\Element\SubmitButton;
use Fwlib\Html\Generator\Element\Text;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RendererTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Form
     */
    protected function buildFormMock(array $methods = null)
    {
        /** @var MockObject|Form $mock */
        $mock = $this->getMock(
            Form::class,
            $methods
        );

        $mock->setId('userForm')
            ->setClass('common-form')
            ->setMethod(Form::METHOD_POST)
            ->setAction('target/url/');

        (new Hidden('a'))->setId('action')
            ->setValue('show')
            ->appendTo($mock);
        (new Text('code'))->setId('userCode')
            ->setClass('common-form__input')
            ->setValue('User Code')
            ->setConfig('tag', 'div')
            ->appendTo($mock);
        (new Text('title'))->setId('userTitle')
            ->setClass('common-form__input')
            ->setTitle('Title')
            ->setValue('User Foo')
            ->setConfig('tag', 'div')
            ->appendTo($mock);

        $submitButton = (new SubmitButton('submit'))->setValue('Submit');
        $mock->getButtons()
            ->add($submitButton);

        return $mock;
    }


    /**
     * @param   string[] $methods
     * @return  MockObject|Renderer
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Renderer::class,
            $methods
        );

        return $mock;
    }


    public function testGetOutputEditMode()
    {
        $renderer = $this->buildMock(['getValidateJs']);

        $form = $this->buildFormMock(['getValidateMessages', 'isValid']);
        $form->expects($this->once())
            ->method('getValidateMessages')
            ->willReturn([
                'code'  => 'Code validate error',
                'title' => 'Title validate error',
            ]);
        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $form->setMode(ElementMode::EDIT);

        $renderer->setForm($form);
        $output = $renderer->getOutput();

        $expected = <<<TAG
<ul class='common-form__validateMessages' id='userForm__validateMessages'>
  <li>Code validate error</li>
  <li>Title: Title validate error</li>
</ul>

<form class='common-form' id='userForm'
  method='post' action='target/url/'>

  <div>
    <input type='hidden' id='action'
      name='a' value='show' />
  </div>

  <div class='common-form__input-container' id='userCode__input-container'>
    <input type='text' class='common-form__input' id='userCode'
      name='code' value='User Code' />
  </div>

  <div class='common-form__input-container' id='userTitle__input-container'>
    <label class='common-form__input__label' id='userTitle__label'
      for='title'>Title</label>
    <input type='text' class='common-form__input' id='userTitle'
      name='title' value='User Foo' />
  </div>

  <div class='common-form__buttons' id='userForm__buttons'>
    <button type='submit'
      name='submit'>
      Submit</button>
  </div>

</form>
TAG;
        $this->assertEquals($expected, $output);
    }


    public function testGetOutputShowMode()
    {
        $renderer = $this->buildMock();

        $form = $this->buildFormMock()
            ->setMode(ElementMode::SHOW);

        $renderer->setForm($form);
        $output = $renderer->getOutput();

        $expected = <<<TAG
<div class='common-form' id='userForm'>

  <div>
    <input type='hidden' id='action'
      name='a' value='show' />
  </div>

  <div class='common-form__input-container' id='userCode__input-container'>
    <div class='common-form__input' id='userCode'>User&nbsp;Code</div>
  </div>

  <div class='common-form__input-container' id='userTitle__input-container'>
    <label class='common-form__input__label' id='userTitle__label'
      for='title'>Title</label>
    <div class='common-form__input' id='userTitle'>User&nbsp;Foo</div>
  </div>

</div>
TAG;
        $this->assertEquals($expected, $output);
    }


    /**
     * @expectedException \Fwlib\Html\Generator\Component\Form\Exception\RendererModeNotImplementedException
     */
    public function testGetOutputWithInvalidMode()
    {
        $form = $this->buildFormMock()
            ->setMode('invalidMode');

        $renderer = $this->buildMock();
        $renderer->setForm($form);

        $renderer->getOutput();
    }


    /**
     * For detailed test, see {@see ValidatorRenderer}.
     */
    public function testGetValidateJs()
    {
        $form = $this->buildFormMock();

        $renderer = $this->buildMock();
        $renderer->setForm($form);

        $output = $this->reflectionCall($renderer, 'getValidateJs');
        $this->assertNotEmpty($output);
    }


    public function testGetValidateMessagesOutput()
    {
        $form = $this->buildFormMock(['isValid']);
        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $renderer = $this->buildMock();
        $renderer->setForm($form);

        $output = $this->reflectionCall($renderer, 'getValidateMessagesOutput');
        $this->assertEquals('', $output);
    }
}
