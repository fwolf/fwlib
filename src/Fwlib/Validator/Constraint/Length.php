<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Config\StringOptions;
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
     * Options:
     * - min
     * - max
     *
     * Boundary included, eg: min=3, 'abc' is valid.
     */
    public function validate($value, StringOptions $options = null)
    {
        parent::validate($value, $options);

        $valid = true;

        $min = $options->get('min', 0);
        $this->messageVariables['min'] = $min;

        if (strlen($value) < $min) {
            $valid = false;
            $this->setMessage('lessThanMin');
        }


        $max = $options->get('max');
        if (false !== $max) {
            $max = intval($max);
            $this->messageVariables['max'] = $max;

            if (strlen($value) > $max) {
                $valid = false;
                $this->setMessage('moreThanMax');
            }
        }

        return $valid;
    }
}
