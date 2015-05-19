<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Config\StringOptions;
use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Required
 *
 * Check by value length after converted to string, so 0 is valid.
 *
 * Array is always valid.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Required extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'default'   => 'The input is required',
    ];


    /**
     * {@inheritdoc}
     */
    public function validate($value, StringOptions $options = null)
    {
        parent::validate($value, $options);

        if (is_array($value)) {
            return true;
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
