<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Ipv4
 *
 * @package     Fwlib\Validator\Constraint
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-04
 */
class Ipv4 extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    public $messageTemplate = array(
        'default'   => 'Invalid ipv4 address'
    );

    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        if (strcmp(long2ip(sprintf("%u", ip2long($value))), $value)) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
