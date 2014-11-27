<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint NotEmpty
 *
 * Bool false and 0 is empty, to allow 0, try constraint Required.
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class NotEmpty extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    public $messageTemplate = array(
        'default'   => 'The input should not be empty or zero'
    );

    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        if (!is_array($value)) {
            $value = trim($value);
        }

        if (empty($value)) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
