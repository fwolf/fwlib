<?php
namespace Fwlib\Html;

use Fwlib\Base\AbstractAutoNewConfig;
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
 *
 * @copyright   Copyright 2011-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-07-21
 */
class FormValidator extends AbstractAutoNewConfig
{
    /**
     * Validate fail messages
     *
     * @var array
     */
    protected $messages = array();

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
    protected $rules = array();


    /**
     * Validator instance
     *
     * @var Validator
     */
    protected $validator = null;


    /**
     * Check rule array, fill empty
     */
    protected function checkRules()
    {
        foreach ($this->rules as $name => &$rule) {
            // title need fill in js

            if (!isset($rule['checkOnBlur'])) {
                $rule['checkOnBlur'] = $this->config['checkOnBlur'];
            }

            if (!isset($rule['checkOnKeyup'])) {
                $rule['checkOnKeyup'] = $this->config['checkOnKeyup'];
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
            $this->rules = array();
            return $this;
        }

        if (!is_array($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $singleName) {
            $singleName = trim($singleName);

            if (empty($part)) {
                $this->rules[$singleName] = array();

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
     * @return  string
     */
    public function getJs()
    {
        $this->checkRules();

        $class = $this->config['class'];
        $id = $this->config['id'];
        $formSelector = $this->config['formSelector'];
        $rules = $this->getUtil('Json')->encodeUnicode($this->rules);
        $checkOnSubmit = ($this->config['checkOnSubmit'])
            ? 'enableCheckOnSubmit()'
            : 'disableCheckOnSubmit()';
        if ($this->config['withClosure']) {
            $closureBegin = '(function () {';
            $closureEnd   = '}) ();';
        } else {
            $closureBegin = '';
            $closureEnd   = '';
        }

        $js = '';

        if ($this->config['withScriptTag']) {
            $js .= "
<script type='text/javascript'>
<!--//--><![CDATA[//>
<!--
";
        }

        $js .= "
$closureBegin

  var $id = $class.createNew();

  $id
    .$checkOnSubmit
    .setForm('$formSelector')
    .setRules($rules)
    .bind();

$closureEnd
";

        if ($this->config['withScriptTag']) {
            $js .= '
//--><!]]>
</script>
';
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
            $this->validator = $this->getService('Validator');
        }

        return $this->validator;
    }


    /**
     * Set default config
     */
    protected function setConfigDefault()
    {
        $this->setConfig(
            array(
                // Class of FormValidator in js
                'class'         => 'FormValidator',

                // Id of FormValidator js instance, should be unique on page.
                'id'            => 'formValidator',

                // jQuery selector for form, should not be empty.
                'formSelector'  => 'form',

                'checkOnBlur'   => true,
                'checkOnKeyup'  => false,
                'checkOnSubmit' => true,

                // Output js with <script> tag
                'withScriptTag' => true,

                // Output js with closure
                // Use closure will free resource, but you can't change
                // validator's configure.
                'withClosure'   => true,
            )
        );
    }


    /**
     * Set validate rule
     *
     * @param   string|array    $name   Name of <input>, or array of it
     * @param   array           $ruleAr
     * @param   bool            $append
     * @return  this
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

            // Other part will be overwrited
            $partAr = array('title', 'tip', 'checkOnBlur', 'checkOnKeyup');
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
        $this->messages = array();

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
