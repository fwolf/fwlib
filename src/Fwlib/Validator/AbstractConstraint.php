<?php
namespace Fwlib\Validator;

use Fwlib\Validator\ConstraintInterface;

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
    protected $message = [];

    /**
     * Validate fail message template
     *
     * Array key is used as 'name' when set message.
     *
     * Array value may use $messageVariable by %messageVariable% format.
     *
     * @var array
     */
    public $messageTemplate = [
        'default'   => 'Validate fail message'
    ];

    /**
     * Variable used in message template
     *
     * These variables come from value for validate and constraintData, should be
     * set in validate().
     *
     * @var array
     */
    protected $messageVariable = [
        'value' => null,
    ];


    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * Set a fail message
     *
     * Will replace %messageVariable% if needed.
     *
     * @param   string  $messageKey
     */
    protected function setMessage($messageKey)
    {
        if (!isset($this->messageTemplate[$messageKey])) {
            throw new \Exception(
                "Validate fail message $messageKey is not defined"
            );
        }

        $message = $this->messageTemplate[$messageKey];

        // Set fail message with a unique key, so if a messageKey is set
        // multiple times, there will only be one message return.
        $messageKey = str_replace('\\', '::', get_class($this)) .
            '::' . $messageKey;
        if (isset($this->message[$messageKey])) {
            return;
        }

        if (!empty($this->messageVariable)) {
            // Need skip if no message variable usable, so should not use
            // 'foreach (array)' here.
            $search = [];
            $replace = [];
            foreach ($this->messageVariable as $k => $v) {
                $search[] = "%$k%";
                $replace[] = is_array($v) ? 'Array' : strval($v);
            }

            $message = str_replace($search, $replace, $message);
        }

        $this->message[$messageKey] = $message;

        return;
    }


    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        // Clear previous message
        $this->message = [];

        // Assign message variable, maybe assign more in inherit class
        $this->messageVariable['value'] = $value;

        // Other validate treatment in inherit class
    }
}
