<?php
namespace Fwlib\Html\Generator\Component\Form;

use Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyTrait;
use Fwlib\Html\Generator\Helper\CheckOnSubmitPropertyTrait;
use Fwlib\Validator\Validator as RealValidator;

/**
 * Validator for form
 *
 * Hold global validate rules and do backend check after form submit.
 *
 *
 * Global checkOn{Keyup|Submit} property will NOT over element setting, they
 * are bond to form itself, not elements.
 *
 *
 * :TODO: Add form level frontend/backend validate closure/js, to do validate
 * which need multiple value, like compare pass and pass again are same.
 * (overall validate)
 *
 * @copyright   Copyright 2011-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Validator
{
    use CheckOnKeyupPropertyTrait;
    use CheckOnSubmitPropertyTrait;


    /**
     * @var Form
     */
    protected $form = null;

    /**
     * Validate fail messages
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Validator instance
     *
     * @var RealValidator
     */
    protected $validator = null;


    /**
     * @return  Form
     */
    public function getForm()
    {
        return $this->form;
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
     * @return  RealValidator
     */
    protected function getValidator()
    {
        if (is_null($this->validator)) {
            $this->validator = new RealValidator();
        }

        return $this->validator;
    }


    /**
     * @param   Form $form
     * @return  static
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }


    /**
     * Validate form data
     *
     * Only check for each element value separately, validate need multiple
     * should do in form level(todo).
     *
     * @return  bool
     */
    public function validate()
    {
        $isValid = true;
        $this->messages = [];

        $validator = $this->getValidator();
        $elements = $this->getForm()->getElements();

        foreach ($elements as $name => $element) {
            $value = $element->getValue();
            $rules = $element->getValidateRules();

            if (!$validator->validate($value, $rules)) {
                $isValid = false;

                // Prefer tip over validate error message
                $message = $element->getTip();
                if (empty($message)) {
                    $message = implode(', ', $validator->getMessages());
                }
                $this->messages[$name] = $message;
            }
        }

        return $isValid;
    }
}
