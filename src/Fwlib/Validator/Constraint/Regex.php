<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Regex
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
        'invalidType' => 'The input must be able to convert to string'
    ];


    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        if (!is_scalar($value)
            && !(is_object($value) && method_exists($value, '__toString'))
        ) {
            $this->setMessage('invalidType');
            return false;
        }

        $value = strval($value);

        if (1 !== preg_match($constraintData, $value)) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
