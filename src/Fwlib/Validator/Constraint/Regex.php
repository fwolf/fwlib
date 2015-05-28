<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Regex
 *
 * Regex string is in field.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Regex extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'default'     => 'The input must fit given regex',
        'emptyRegex'  => 'Empty regex string',
        'invalidType' => 'The input must be able to convert to string'
    ];


    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        parent::validate($value);

        if (!is_scalar($value)
            && !(is_object($value) && method_exists($value, '__toString'))
        ) {
            $this->setMessage('invalidType');
            return false;
        }

        $value = strval($value);
        $regex = $this->getField();

        if (empty($regex)) {
            $this->setMessage('emptyRegex');
            return false;
        }

        if (1 !== preg_match($regex, $value)) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
