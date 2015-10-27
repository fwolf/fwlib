<?php
namespace Fwlib\Html\Generator\Component\Form;

use Fwlib\Html\Exception\MissingClassAndIdException;
use Fwlib\Html\Generator\Component\Form\Helper\FormAwareTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Validator js renderer for form
 *
 * Uses jQuery.
 *
 *
 * :TODO: Add form level frontend/backend validate closure/js, to do validate
 * which need multiple value, like compare pass and pass again are same.
 * (overall validate)
 *
 * @copyright   Copyright 2011-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ValidatorRenderer
{
    use UtilContainerAwareTrait;
    use FormAwareTrait;


    /**
     * Class of FormValidator in js
     *
     * @var string
     */
    protected $jsClass = 'FormValidator';

    /**
     * Id of FormValidator js instance
     *
     * Should be unique on page, so append with form id.
     *
     * @var string
     */
    protected $jsId = 'formValidator';

    /**
     * Validate rules
     *
     * Rules need check through js, so only below constraintName supported:
     * - regex
     * - required
     * - url
     *
     * {
     *  name: {
     *      title,      // <Input> title for print friendly fail message, will
     *                  // try to find in context if not set.
     *      check,      // Constraint string or array of it.
     *      tip,        // Show when input and validate fail, replace fail
     *                  // message from validator.
     *      // :TODO: Below to can move to element config
     *      checkOnBlur,    // Bool, use config if not set.
     *      checkOnKeyup,   // Bool, use config if not set.
     *  }
     * }
     *
     * The name is name of form input element, they can be different with id.
     * Backend validate will read value by $_POST['name'], while frontend
     * validate will read value by $(input[name='name'], $form).val(), or
     * $(textarea[name='name'], $form).val().
     *
     * @var array
     * @deprecated  Rule are bond to element now
     */
    protected $rules = [];


    /**
     * Apply closure clause to js
     *
     * @param   string $jsStr
     * @return  string
     */
    protected function applyJsClosure($jsStr)
    {
        $jsStr = $this->getUtilContainer()->getString()
            ->indent($jsStr, 2);

        $jsStr = "(function (global) {
$jsStr
}) (window);";

        return $jsStr;
    }


    /**
     * Apply script tab to js, this should the last apply method used
     *
     * @param   string $jsStr
     * @return  string
     */
    protected function applyJsScriptTag($jsStr)
    {
        $html = <<<TAG
<script type='text/javascript'>
<!--

$jsStr

-->
</script>
TAG;

        return $html;
    }


    /**
     * @return  string
     * @throws  MissingClassAndIdException
     */
    protected function getFormSelector()
    {
        $form = $this->getForm();
        $formId = $form->getId();
        $class = $form->getClass();

        if (!empty($formId)) {
            return "#{$formId}";

        } elseif (!empty($class)) {
            return ".{$class}";

        } else {
            throw new MissingClassAndIdException(
                'Need id or class as form selector'
            );
        }
    }


    /**
     * @return  string
     */
    protected function getJsId()
    {
        $form = $this->getForm();
        $formId = $form->getId();

        $suffix = empty($formId) ? substr(md5(serialize($form)), 0, 6)
            : $formId;

        return "{$this->jsId}_{$suffix}";
    }


    /**
     * Get rules in json format
     *
     * @return  string
     */
    protected function getJsonRules()
    {
        $rules = [];
        foreach ($this->getForm()->getElements() as $name => $element) {
            $rules[$name] = [
                'title'        => $element->getTitle(),
                'check'        => $element->getValidateRules(),
                'tip'          => $element->getTip(),
                'checkOnBlur'  => $element->isCheckOnBlur(),
                'checkOnKeyup' => $element->isCheckOnKeyup(),
            ];
        }

        return $this->getUtilContainer()->getJson()
            ->encodeUnicode($rules);
    }


    /**
     * Get validate js
     *
     * @param   boolean $withScriptTag  Output js with <script> tag
     * @param   boolean $withClosure    Output js with closure
     *                                  Use closure will avoid memory leak. To
     *                                  use formValidator object in other js,
     *                                  import it from window object.
     * @return  string
     */
    public function getOutput($withScriptTag = true, $withClosure = true)
    {
        $jsClass = $this->jsClass;
        $jsId = $this->getJsId();
        $formSelector = $this->getFormSelector();
        $rules = $this->getJsonRules();

        $validator = $this->getForm()->getValidator();
        $checkOnSubmit = ($validator->isCheckOnSubmit())
            ? 'enableCheckOnSubmit()'
            : 'disableCheckOnSubmit()';

        $jsStr = <<<TAG

global.$jsId = $jsClass.createNew();

global.$jsId
  .$checkOnSubmit
  .setForm('$formSelector')
  .setRules($rules)
  .bind();

TAG;

        foreach (array_keys($validator->getMessages()) as $name) {
            $jsStr .= <<<TAG

global.$jsId.markFailed(global.$jsId.getInput('$name'));

TAG;
        }

        if ($withClosure) {
            $jsStr = $this->applyJsClosure($jsStr);
        }

        if ($withScriptTag) {
            $jsStr = $this->applyJsScriptTag($jsStr);
        }

        return $jsStr;
    }
}
