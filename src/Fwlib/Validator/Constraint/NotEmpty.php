<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint NotEmpty
 *
 * Bool false and 0 is empty, to allow 0, try constraint Required.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class NotEmpty extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'default'   => 'The input should not be empty or zero'
    ];


    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        parent::validate($value);

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
