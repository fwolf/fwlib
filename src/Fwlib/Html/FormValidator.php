<?php
namespace Fwlib\Html;

use Fwlib\Util\UtilContainerAwareTrait;
use Fwlib\Validator\Validator;

/**
 * Validator for form
 *
 * Will generate validate js on web frontend, and do backend check after form
 * submit.
 *
 * The validate js uses jQuery.
 *
 * @codeCoverageIgnore
 * @deprecated  Use new {@see Form}, keep for back compatible.
 *
 * @copyright   Copyright 2011-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FormValidator
{
    use UtilContainerAwareTrait;


    /**
     * Do check when input blur
     *
     * @var boolean
     */
    protected $checkOnBlur = true;

    /**
     * Do check when user input something
     *
     * @var boolean
     */
    protected $checkOnKeyup = false;

    /**
     * Do check before form submit
     *
     * @var boolean
     */
    protected $checkOnSubmit = true;

    /**
     * jQuery selector for form, should not be empty.
     *
     * @var string
     */
    protected $formSelector = 'form';

    /**
     * Class of FormValidator in js
     *
     * @var string
     */
    protected $jsClass = 'FormValidator';

    /**
     * Id of FormValidator js instance, should be unique on page.
     *
     * @var string
     */
    protected $jsId = 'formValidator';

    /**
     * Validate fail messages
     *
     * @var array
     */
    protected $messages = [];

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
     */
    protected $rules = [];

    /**
     * Validator instance
     *
     * @var Validator
     */
    protected $validator = null;


    /**
     * Apply closure clause to js
     *
     * @param   string  $js
     * @return  string
     */
    protected function applyJsClosure($js)
    {
        $js = $this->getUtilContainer()->getString()
            ->indent($js, 2);

        $js = "(function (global) {
$js
}) (window);";

        return $js;
    }


    /**
     * Apply script tab to js, this should the last apply method used
     *
     * @param   string  $js
     * @return  string
     */
    protected function applyJsScriptTag($js)
    {
        $js = "<script type='text/javascript'>
<!--

$js

-->
</script>";

        return $js;
    }


    /**
     * Check rule array, fill empty
     */
    protected function checkRules()
    {
        foreach ($this->rules as $name => &$rule) {
            // title need fill in js

            if (!isset($rule['checkOnBlur'])) {
                $rule['checkOnBlur'] = $this->checkOnBlur;
            }

            if (!isset($rule['checkOnKeyup'])) {
                $rule['checkOnKeyup'] = $this->checkOnKeyup;
            }
        }
    }


    /**
     * Clear rule on all or some id
     *
     * @param   string|array    $name   String or array of it, empty means all
     * @param   string|array    $part   Empty means all part
     * @return  this
     */
    public function clearRules($name = '*', $part = '')
    {
        if ('*' == $name) {
            $this->rules = [];
            return $this;
        }

        if (!is_array($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $singleName) {
            $singleName = trim($singleName);

            if (empty($part)) {
                $this->rules[$singleName] = [];

            } else {
                if (!is_array($part)) {
                    $part = implode(',', $part);
                }

                foreach ($part as $singlePart) {
                    unset($this->rules[$singleName][$singlePart]);
                }
            }
        }

        return $this;
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
    public function getJs($withScriptTag = true, $withClosure = true)
    {
        $this->checkRules();

        $class = $this->jsClass;
        $id = $this->jsId;
        $formSelector = $this->formSelector;
        $rules = $this->jsonEncode($this->rules);
        $checkOnSubmit = ($this->checkOnSubmit)
            ? 'enableCheckOnSubmit()'
            : 'disableCheckOnSubmit()';

        $js = "
global.$id = $class.createNew();

global.$id
  .$checkOnSubmit
  .setForm('$formSelector')
  .setRules($rules)
  .bind();
";

        foreach (array_keys($this->messages) as $name) {
            $js .= "
global.$id.markFailed(global.$id.getInput('$name'));
";
        }

        if ($withClosure) {
            $js = $this->applyJsClosure($js);
        }

        if ($withScriptTag) {
            $js = $this->applyJsScriptTag($js);
        }

        return $js;
    }


    /**
     * Get validate fail message
     *
     * @return  array
     */
    public function getMessages()
    {
        return $this->messages;
    }


    /**
     * Get Validator instance
     *
     * @return  Validator
     */
    protected function getValidator()
    {
        if (is_null($this->validator)) {
            $this->validator = new Validator;
        }

        return $this->validator;
    }


    /**
     * Encode a string to json, for used as object in js
     *
     * @param   array
     * @return  string
     */
    protected function jsonEncode($value)
    {
        return $this->getUtilContainer()->getJson()
            ->encodeUnicode($value);
    }


    /**
     * Setter of checkOnSubmit
     *
     * @param   boolean $checkOnSubmit
     * @return  FormValidator
     */
    public function setCheckOnSubmit($checkOnSubmit)
    {
        $this->checkOnSubmit = $checkOnSubmit;

        return $this;
    }


    /**
     * Setter of formSelector
     *
     * @param   string  $formSelector
     * @return  FormValidator
     */
    public function setFormSelector($formSelector)
    {
        $this->formSelector = $formSelector;

        return $this;
    }


    /**
     * Set validate rule
     *
     * @param   string|array    $name   Name of <input>, or array of it
     * @param   array           $ruleAr
     * @param   bool            $append
     * @return  FormValidator
     */
    public function setRule($name, $ruleAr, $append = true)
    {
        if (!is_array($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $singleName) {
            $singleName = trim($singleName);

            // May append to check array
            if (isset($ruleAr['check']) && $append) {
                foreach ((array)$ruleAr['check'] as $rule) {
                    $this->rules[$singleName]['check'][] = $rule;
                }
            } else {
                $this->rules[$singleName]['check'] = $ruleAr['check'];
            }

            // Other part will be overwritten
            $partAr = [
                'title', 'tip', 'checkOnBlur', 'checkOnKeyup',
                'puppet'
            ];
            foreach ($partAr as $part) {
                if (isset($ruleAr[$part])) {
                    $this->rules[$singleName][$part] = $ruleAr[$part];
                }
            }
        }

        return $this;
    }


    /**
     * Set whole validate rule array
     *
     * @param   array   $rules
     * @return  $this
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }


    /**
     * Set validate rule, simpler way
     *
     * @param   string|array    $name   String or array of it
     * @param   string|array    $check  String or array of it
     * @param   string          $tip
     * @return  this
     */
    public function setRuleSimple($name, $check, $tip = '')
    {
        if (!is_array($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $singleName) {
            $singleName = trim($singleName);

            $this->rules[$singleName]['check'] = (array)$check;
            $this->rules[$singleName]['tip']   = $tip;
        }

        return $this;
    }


    /**
     * Setter of validator
     *
     * @param   Validator   $validator
     * @return  FormValidator
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }


    /**
     * Validate form data
     *
     * Un-used rule(not exists in formData index) will be ignored.
     *
     * @param   array   $formData
     * @return  bool
     */
    public function validate($formData)
    {
        $isValid = true;
        $this->messages = [];

        foreach ($this->rules as $name => $ruleContent) {
            if (!isset($formData[$name])) {
                continue;
            }

            $check = $ruleContent['check'];
            $nameIsValid = true;

            // Check may be array, and constraint Url need all formData array,
            // other constraint only need formData[name], so we got loop here.
            foreach ((array)$check as $checkString) {
                $nameIsValid = $this->getValidator()->validate(
                    ('url' == substr($checkString, 0, 3))
                    ? $formData
                    : $formData[$name],
                    $checkString
                );

                if (!$nameIsValid) {
                    $nameIsValid = false;
                    break;
                }
            }

            if (!$nameIsValid) {
                $isValid = false;
                $this->messages[$name] = $ruleContent['tip'];
                // Continue to check other name
            }
        }

        return $isValid;
    }
}
