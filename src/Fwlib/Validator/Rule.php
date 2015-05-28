<?php
namespace Fwlib\Validator;

use Fwlib\Config\StringOptions;
use Fwlib\Validator\Exception\InvalidRuleStringException;
use Fwlib\Validator\Helper\FieldAndOptionsPropertyTrait;

/**
 * Validate Rule
 *
 * A rule contain at most 3 parts, in format:
 *
 *  type[ field][: optionString]
 *
 * Type corresponding constraint class name, usually camelCased.
 *
 * Field is supplement of type, type notEmpty does not need it, while type
 * regex and url need it to store regex or url string.
 *
 * The third part is option string, of {@see StringOptions} format.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Rule
{
    use FieldAndOptionsPropertyTrait;


    /**
     * Separator between type and field
     */
    const FIELD_SEPARATOR = ' ';

    /**
     * Separator between type/field and option string
     *
     * Notice the space, its used to avoid conflict with url 'http://...'.
     */
    const OPTION_SEPARATOR = ': ';


    /**
     * @var string
     */
    protected $type = '';


    /**
     * @param   string  $ruleString
     * @param   string  $fieldSeparator
     * @param   string  $optionSeparator
     */
    public function __construct(
        $ruleString = '',
        $fieldSeparator = self::FIELD_SEPARATOR,
        $optionSeparator = self::OPTION_SEPARATOR
    ) {
        if (!empty($ruleString)) {
            $this->import($ruleString, $fieldSeparator, $optionSeparator);
        }
    }


    /**
     * Clear all content
     *
     * @return  static
     */
    protected function clear()
    {
        $this->type = '';
        $this->setField('');
        $this->setOptionsInstance(null);

        return $this;
    }


    /**
     * Export back to string
     *
     * @param   string  $fieldSeparator
     * @param   string  $optionSeparator
     * @return  string
     */
    public function export(
        $fieldSeparator = self::FIELD_SEPARATOR,
        $optionSeparator = self::OPTION_SEPARATOR
    ) {
        $fieldPart = empty($this->field) ? ''
            : $fieldSeparator . $this->field;

        $optionPart = is_null($this->optionsInstance) ? ''
            : $optionSeparator . $this->optionsInstance->export();

        return $this->type . $fieldPart . $optionPart;
    }


    /**
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Initialize from rule string
     *
     * Will clear present values.
     *
     * @param   string  $ruleString
     * @param   string  $fieldSeparator
     * @param   string  $optionSeparator
     * @return  static
     * @throws  \Fwlib\Validator\Exception\InvalidRuleStringException
     */
    public function import(
        $ruleString = '',
        $fieldSeparator = self::FIELD_SEPARATOR,
        $optionSeparator = self::OPTION_SEPARATOR
    ) {
        $this->clear();
        $originalRuleString = $ruleString;

        if (false !== strpos($ruleString, $optionSeparator)) {
            $optionString = strstr($ruleString, $optionSeparator, false);
            $optionString = substr($optionString, strlen($optionSeparator));
            $this->setOptionsInstance(new StringOptions($optionString));

            $ruleString = strstr($ruleString, $optionSeparator, true);
        }

        if (false !== strpos($ruleString, $fieldSeparator)) {
            $field = strstr($ruleString, $fieldSeparator, false);
            $field = substr($field, strlen($fieldSeparator));
            $this->setField(trim($field));

            $ruleString = strstr($ruleString, $fieldSeparator, true);
        }

        $ruleString = trim($ruleString);
        if (empty($ruleString)) {
            throw new InvalidRuleStringException(
                "Format error in rule string '$originalRuleString'"
            );
        } else {
            $this->type = $ruleString;
        }

        return $this;
    }
}
