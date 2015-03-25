<?php
namespace Fwlib\Validator;

use Fwlib\Validator\Exception\MessageTemplateNotDefinedException;

/**
 * Constraint
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractConstraint implements ConstraintInterface
{
    /**
     * Validate fail message
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Validate fail message template
     *
     * Array key is used as template name, and max one instance per name can
     * occur in result message, as same key cannot exist in associate array.
     *
     * Array value may use $messageVariable by %messageVariable% format.
     *
     * @var array
     */
    protected $messageTemplates = [
        'default'   => 'Validate failed'
    ];

    /**
     * Variable used in message template
     *
     * These variables come from value for validate and constraintData, should
     * be set during validate.
     *
     * @var array
     */
    protected $messageVariables = [
        'value' => null,
    ];


    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->messages;
    }


    /**
     * Set one fail message
     *
     * Will replace %messageVariable% if needed.
     *
     * @param   string  $templateName
     * @throws  MessageTemplateNotDefinedException
     */
    protected function setMessage($templateName)
    {
        if (!isset($this->messageTemplates[$templateName])) {
            throw new MessageTemplateNotDefinedException(
                "Message template \"$templateName\" is not defined"
            );
        }

        $message = $this->messageTemplates[$templateName];

        // Set fail message with a unique key, so if a messageKey is set
        // multiple times, there will only be one message return.
        $messageKey = str_replace('\\', '::', get_class($this)) .
            '::' . $templateName;
        if (isset($this->messages[$messageKey])) {
            return;
        }

        if (!empty($this->messageVariables)) {
            // Need skip if no message variable usable, so should not use
            // 'foreach (array)' here.
            $search = [];
            $replace = [];
            foreach ($this->messageVariables as $k => $v) {
                $search[] = "%$k%";
                $replace[] = is_array($v) ? 'Array' : strval($v);
            }

            $message = str_replace($search, $replace, $message);
        }

        $this->messages[$messageKey] = $message;

        return;
    }


    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        // Clear previous message
        $this->messages = [];

        // Assign message variable, maybe assign more in inherit class
        $this->messageVariables['value'] = $value;

        // Other validate treatment in inherit class
    }
}
