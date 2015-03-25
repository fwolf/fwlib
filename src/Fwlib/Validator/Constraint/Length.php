<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Length
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Length extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'lessThanMin'   => 'The input should be more than %min% characters',
        'moreThanMax'   => 'The input should be less than %max% characters',
    ];


    /**
     * {@inheritdoc}
     *
     * $constraintData format:
     * - minLength
     * - minLength, maxLength
     * - minLength to maxLength
     *
     * If need not check minLength, set it to 0.
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);


        // Get min and max
        $constraintData = str_ireplace('to', ',', $constraintData);
        $parts = explode(',', $constraintData);

        $min = intval(array_shift($parts));
        $this->messageVariables['min'] = $min;

        if (empty($parts)) {
            $max = null;
        } else {
            $max = intval(array_shift($parts));
            $this->messageVariables['max'] = $max;
        }


        $valid = true;

        if (strlen($value) < $min) {
            $valid = false;
            $this->setMessage('lessThanMin');
        }

        if (!is_null($max) && strlen($value) > $max) {
            $valid = false;
            $this->setMessage('moreThanMax');
        }

        return $valid;
    }
}
