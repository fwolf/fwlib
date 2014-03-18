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
     * Validate fail message
     *
     * @var array
     */
    protected $message = array();

    /**
     * Validate rule
     *
     * Rule need check through js, so only below constraintName supported:
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
    protected $rule = array();


    /**
     * Validator instance
     *
     * @var Validator
     */
    protected $validator = null;


    /**
     * Check rule array, fill empty
     */
    protected function checkRule()
    {
        foreach ($this->rule as $name => &$rule) {
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
    public function clearRule($name = '*', $part = '')
    {
        if ('*' == $name) {
            $this->rule = array();
            return $this;
        }

        if (!is_array($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $singleName) {
            $singleName = trim($singleName);

            if (empty($part)) {
                $this->rule[$singleName] = array();

            } else {
                if (!is_array($part)) {
                    $part = implode(',', $part);
                }

                foreach ($part as $singlePart) {
                    unset($this->rule[$singleName][$singlePart]);
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
        $this->checkRule();

        $class = $this->config['class'];
        $id = $this->config['id'];
        $formSelector = $this->config['formSelector'];
        $rule = $this->getUtil('Json')->encodeUnicode($this->rule);
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
    .setRule($rule)
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
    public function getMessage()
    {
        return $this->message;
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
                    $this->rule[$singleName]['check'][] = $rule;
                }
            } else {
                $this->rule[$singleName]['check'] = $ruleAr['check'];
            }

            // Other part will be overwrited
            $partAr = array('title', 'tip', 'checkOnBlur', 'checkOnKeyup');
            foreach ($partAr as $part) {
                if (isset($ruleAr[$part])) {
                    $this->rule[$singleName][$part] = $ruleAr[$part];
                }
            }
        }

        return $this;
    }


    /**
     * Set whole validate rule array
     *
     * @param   array   $ruleArray
     * @return  $this
     */
    public function setRuleArray($ruleArray)
    {
        $this->rule = $ruleArray;

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

            $this->rule[$singleName]['check'] = (array)$check;
            $this->rule[$singleName]['tip']   = $tip;
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
        $this->message = array();

        foreach ($this->rule as $name => $ruleContent) {
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
                $this->message[$name] = $ruleContent['tip'];
                // Continue to check other name
            }
        }

        return $isValid;
    }
}
