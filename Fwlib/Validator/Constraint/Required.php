<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Required
 *
 * Check by value length after converted to string, so 0 is valid.
 *
 * @package     Fwlib\Validator\Constraint
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-11
 */
class Required extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    public $messageTemplate = array(
        'default'   => 'The input is required',
        'array'     => 'Array is not suit for constraint Required',
    );

    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        if (is_array($value)) {
            $this->setMessage('array');
            return false;
        }

        $value = trim((string)$value);

        if (strlen($value) == 0) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
