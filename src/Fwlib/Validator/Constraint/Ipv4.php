<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Ipv4
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Ipv4 extends AbstractConstraint
{
    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'default'   => 'The input should be valid ipv4 address'
    ];


    /**
     * {@inheritdoc}
     */
    protected function doValidate($value)
    {
        if (strcmp(long2ip(sprintf("%u", ip2long($value))), $value)) {
            $this->setMessage('default');
            return false;
        } else {
            return true;
        }
    }
}
