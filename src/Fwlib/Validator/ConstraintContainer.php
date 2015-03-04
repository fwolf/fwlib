<?php
namespace Fwlib\Validator;

use Fwlib\Base\ServiceContainerTrait;
use Fwlib\Validator\Constraint\Email;
use Fwlib\Validator\Constraint\Ipv4;
use Fwlib\Validator\Constraint\Length;
use Fwlib\Validator\Constraint\NotEmpty;
use Fwlib\Validator\Constraint\Regex;
use Fwlib\Validator\Constraint\Required;
use Fwlib\Validator\Constraint\Url;

/**
 * Validate constraint container
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConstraintContainer implements ConstraintContainerInterface
{
    use ServiceContainerTrait;


    /**
     * @return  Email
     */
    public function getEmail()
    {
        return $this->get('Email');
    }


    /**
     * {@inheritdoc}
     */
    protected function getInitialServiceClassMap()
    {
        return [
            'Email'     => Email::class,
            'Ipv4'      => Ipv4::class,
            'Length'    => Length::class,
            'NotEmpty'  => NotEmpty::class,
            'Required'  => Required::class,
            'Regex'     => Regex::class,
            'Url'       => Url::class,
        ];
    }


    /**
     * @return  Ipv4
     */
    public function getIpv4()
    {
        return $this->get('Ipv4');
    }


    /**
     * @return  Length
     */
    public function getLength()
    {
        return $this->get('Length');
    }


    /**
     * @return  NotEmpty
     */
    public function getNotEmpty()
    {
        return $this->get('NotEmpty');
    }


    /**
     * @return  Regex
     */
    public function getRegex()
    {
        return $this->get('Regex');
    }


    /**
     * @return  Required
     */
    public function getRequired()
    {
        return $this->get('Required');
    }


    /**
     * @return  Url
     */
    public function getUrl()
    {
        return $this->get('Url');
    }
}
