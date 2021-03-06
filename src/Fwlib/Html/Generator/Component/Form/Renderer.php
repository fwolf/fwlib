<?php
namespace Fwlib\Html\Generator\Component\Form;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\Component\Form\Exception\RendererModeNotImplementedException;
use Fwlib\Html\Generator\Component\Form\Helper\FormAwareTrait;
use Fwlib\Html\Generator\Element\Hidden;
use Fwlib\Html\Generator\ElementCollection;
use Fwlib\Html\Generator\ElementInterface;
use Fwlib\Html\Generator\UploadFileElementInterface;
use Fwlib\Html\Helper\ClassAndIdHtmlTrait;
use Fwlib\Html\Helper\IndentAwareTrait;

/**
 * Form renderer
 *
 * @copyright   Copyright 2014-2016 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Renderer
{
    use IndentAwareTrait;
    use ClassAndIdHtmlTrait;
    use FormAwareTrait;


    /**
     * @var Hidden[]
     */
    protected $hiddenElements = [];

    /**
     * @var AbstractElement[]
     */
    protected $visualElements = [];


    /**
     * @return  string
     */
    protected function getButtonsOutput()
    {
        $buttons = $this->getForm()->getButtons();

        $form = $this->getForm();
        $buttons->setContainerClass($form->getClass('__buttons'))
            ->setContainerId($form->getId('__buttons'));

        $output = $buttons->getOutput();
        $output = trim($output);

        return $this->indent($output, 2);
    }


    /**
     * @return  string
     */
    protected function getElementContainerCloseTag()
    {
        return '</div>';
    }


    /**
     * @param   ElementInterface $element
     * @return  string
     */
    protected function getElementContainerOpenTag($element)
    {
        $form = $this->getForm();

        $classHtml = $this->getClassHtml($form->getClass('__input-container'));
        $idHtml = $this->getIdHtml($element->getId('__input-container'));

        return <<<TAG
<div{$classHtml}{$idHtml}>
TAG;
    }


    /**
     * @param   ElementInterface $element
     * @return  string
     */
    protected function getElementLabelHtml($element)
    {
        if (empty($element->getTitle())) {
            return '';
        }

        $form = $this->getForm();

        $labelClass = $form->getClass('__input__label');
        $classHtml = $this->getClassHtml($labelClass);
        $labelId = $element->getId('__label');
        $idHtml = $this->getIdHtml($labelId);

        $name = $element->getName();
        $title = $element->getTitle();

        $output = <<<TAG
<label{$classHtml}{$idHtml}
  for='{$name}'>{$title}</label>
TAG;

        return $output;
    }


    /**
     * Return enc type string if form has file upload element
     *
     * @return  string
     */
    protected function getEncTypeHtml()
    {
        $found = false;
        foreach ($this->getForm()->getElements() as $element) {
            if ($element instanceof UploadFileElementInterface) {
                $found = true;
                break;
            }
        }

        /** @noinspection SpellCheckingInspection */

        return ($found) ? " enctype='multipart/form-data'" : '';
    }


    /**
     * @return  string
     */
    protected function getHiddenElementsOutput()
    {
        $collection = new ElementCollection();
        $collection->setElements($this->hiddenElements)
            ->setMode($this->getForm()->getMode());

        $output = $collection->getOutput();
        $output = $this->indent($output, 2);

        $output = "<div>\n" . $output . "\n</div>";

        return $this->indent($output, 2);
    }


    /**
     * @return  string
     * @throws  RendererModeNotImplementedException
     */
    public function getOutput()
    {
        $this->splitHiddenElements();

        $form = $this->getForm();
        $mode = ucfirst($form->getMode());
        $method = "getOutputPartsFor{$mode}Mode";

        if (method_exists($this, $method)) {
            $parts = $this->$method();

        } else {
            throw new RendererModeNotImplementedException(
                "Form renderer for '{$mode}' mode is not implemented"
            );
        }

        $output = $this->joinParts($parts, $form->getIndent());

        return $output;
    }


    /**
     * @return  string
     */
    protected function getOutputCloseTag()
    {
        return '</div>';
    }


    /**
     * @return  string
     */
    protected function getOutputFormCloseTag()
    {
        return '</form>';
    }


    /**
     * @return  string
     */
    protected function getOutputFormOpenTag()
    {
        $form = $this->getForm();

        $encTypeHtml = $this->getEncTypeHtml();

        $classHtml = $this->getClassHtml($form->getClass());
        $idHtml = $this->getIdHtml($form->getId());

        $method = $form->getMethod();
        $action = $form->getAction();

        $output = <<<TAG
<form{$encTypeHtml}{$classHtml}{$idHtml}
  method='{$method}' action='{$action}'>
TAG;

        return $output;
    }


    /**
     * @return  string
     */
    protected function getOutputOpenTag()
    {
        $form = $this->getForm();

        $classHtml = $this->getClassHtml($form->getClass());
        $idHtml = $this->getIdHtml($form->getId());

        return "<div" . $classHtml . $idHtml . ">";
    }


    /**
     * @return  string[]
     */
    protected function getOutputPartsForEditMode()
    {
        return [
            'validateMessages' => $this->getValidateMessagesOutput(),
            'formOpenTag'      => $this->getOutputFormOpenTag(),
            'hiddenElements'   => $this->getHiddenElementsOutput(),
            'visualElements'   => $this->getVisualElementsOutput(),
            'buttons'          => $this->getButtonsOutput(),
            'formCloseTag'     => $this->getOutputFormCloseTag(),
            'validateJs'       => $this->getValidateJs(),
        ];
    }


    /**
     * @return  string[]
     */
    protected function getOutputPartsForShowMode()
    {
        return [
            'openTag'        => $this->getOutputOpenTag(),
            'hiddenElements' => $this->getHiddenElementsOutput(),
            'visualElements' => $this->getVisualElementsOutput(),
            'closeTag'       => $this->getOutputCloseTag(),
        ];
    }


    /**
     * Get js to do form validate
     *
     * @return  string
     */
    protected function getValidateJs()
    {
        $renderer = new ValidatorRenderer();
        $renderer->setForm($this->getForm());

        return $renderer->getOutput();
    }


    /**
     * Get html of validate fail messages
     *
     * @return  string
     */
    protected function getValidateMessagesOutput()
    {
        $form = $this->getForm();

        if ($form->isValid()) {
            return '';
        }

        $messages = $form->getValidateMessages();

        $output = $this->renderValidateMessages($messages);

        return $output;
    }


    /**
     * @return  string
     */
    protected function getVisualElementsOutput()
    {
        $form = $this->getForm();
        $mode = $form->getMode();
        $separator = $form->getSeparator();

        $output = '';
        foreach ($this->visualElements as $element) {
            $element = $form->prepare($element);

            $labelHtml = $this->getElementLabelHtml($element);

            $elementHtml = $labelHtml . $separator . $element->getOutput($mode);
            $elementHtml = trim($elementHtml);  // For empty label

            $elementHtml = $this->indent($elementHtml, 2);

            $elementHtml = <<<TAG
{$this->getElementContainerOpenTag($element)}
{$elementHtml}
{$this->getElementContainerCloseTag()}

TAG;

            $output .= $elementHtml . $separator;
        }
        $output = trim($output);

        $output = $this->indent($output, 2);

        return $output;
    }


    /**
     * Join non-empty parts and do indent
     *
     * @param   string[] $parts
     * @param   int      $indent
     * @return  string
     */
    protected function joinParts(array $parts, $indent = 0)
    {
        $output = '';
        $separator = str_repeat($this->getForm()->getSeparator(), 2);
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            $output .= $part . $separator;
        }
        $output = trim($output);

        $output = $this->indentHtml($output, $indent);

        return $output;
    }


    /**
     * @param   string[] $messages {name: message}
     * @return  string
     */
    protected function renderValidateMessages($messages)
    {
        $form = $this->getForm();

        $liHtml = '';
        foreach ($messages as $name => $message) {
            $elementTitle = $form->getElement($name)
                ->getTitle();
            if (!empty($elementTitle)) {
                $elementTitle .= ': ';
            }
            $liHtml .= "<li>{$elementTitle}{$message}</li>\n";
        }

        $liHtml = trim($liHtml);
        $liHtml = $this->indent($liHtml, 2);

        $classHtml = $this->getClassHtml($form->getClass('__validateMessages'));
        $idHtml = $this->getIdHtml($form->getId('__validateMessages'));

        $output = <<<TAG
<ul{$classHtml}{$idHtml}>
{$liHtml}
</ul>
TAG;

        return trim($output);
    }


    /**
     * Separate hidden elements vs visual
     */
    protected function splitHiddenElements()
    {
        $this->hiddenElements = [];
        $this->visualElements = [];

        $elements = $this->getForm()->getElements();

        foreach ($elements as $key => $element) {
            if (is_a($element, Hidden::class)) {
                $this->hiddenElements[$key] = $element;
            } else {
                $this->visualElements[$key] = $element;
            }
        }
    }
}
