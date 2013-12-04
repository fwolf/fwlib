<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint NotEmpty
 *
 * @package     Fwlib\Validator\Constraint
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-03
 */
class NotEmpty extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    public $messageTemplate = array(
        'default'   => 'The input should not be empty'
    );

    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        if (empty($value)) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
