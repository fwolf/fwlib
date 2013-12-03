<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Length
 *
 * @package     Fwlib\Validator\Constraint
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
 */
class Length extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    public $messageTemplate = array(
        'lessThanMin'   => 'The input is less than %min% characters',
        'moreThanMax'   => 'The input is more than %max% characters',
    );

    /**
     * {@inheritdoc}
     *
     * $ruleData format:
     * - minLength
     * - minLength, maxLength
     * - minLength to maxLength
     *
     * If need not check minLength, set it to 0.
     */
    public function validate($value, $ruleData = null)
    {
        parent::validate($value, $ruleData);


        // Get min and max
        $ruleData = str_ireplace('to', ',', $ruleData);
        $ar = explode(',', $ruleData);

        $min = intval(array_shift($ar));
        $this->messageVariable['min'] = $min;

        if (empty($ar)) {
            $max = null;
        } else {
            $max = intval(array_shift($ar));
            $this->messageVariable['max'] = $max;
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
