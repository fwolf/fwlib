<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Util\UtilContainerAwareTrait;
use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Email
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Email extends AbstractConstraint
{
    use UtilContainerAwareTrait;


    /**
     * Check email domain through dns
     *
     * @var bool
     */
    protected $dnsCheck = false;


    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'default'   => 'The input should be valid email address'
    ];


    /**
     * Setter of $dnsCheck
     *
     * @param   boolean $dnsCheck
     * @return  static
     */
    public function setDnsCheck($dnsCheck)
    {
        $this->dnsCheck = $dnsCheck;

        return $this;
    }


    /**
     * {@inheritdoc}
     *
     * @link http://www.linuxjournal.com/article/9585
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        $atIndex = strrpos($value, '@');
        if (false === $atIndex) {
            return false;
        }

        $domain = substr($value, $atIndex + 1);
        $local = substr($value, 0, $atIndex);

        $valid = $this->validateDomainPart($domain) &&
            $this->validateLocalPart($local);

        // Some network provider will return fake A record if a dns query
        // return fail, usually display some ads, so we only check MX record.
        if ($valid && $this->dnsCheck &&
            $this->getUtilContainer()->getEnv()->isNixOs() &&
            !checkdnsrr($domain, 'MX')
        ) {
            $valid = false;
        }

        if (!$valid) {
            $this->setMessage('default');
        }

        return $valid;
    }


    /**
     * @param   string  $domain
     * @return  bool
     */
    protected function validateDomainPart($domain)
    {
        $valid = true;
        $length = strlen($domain);

        if ($length < 1 || $length > 255) {
            // domain part length exceeded
            $valid = false;

        } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $valid = false;

        } elseif (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $valid = false;
        }

        return $valid;
    }


    /**
     * @param   string  $local
     * @return  bool
     */
    protected function validateLocalPart($local)
    {
        $valid = true;
        $length = strlen($local);

        if ($length < 1 || $length > 64) {
            // local part length exceeded
            $valid = false;

        } elseif ($local[0] == '.' || $local[$length-1] == '.') {
            // local part starts or ends with '.'
            $valid = false;

        } elseif (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $valid = false;

        } elseif (!preg_match(
            '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
            str_replace("\\\\", "", $local)
        )) {
            // Character not valid in local part unless local part is quoted
            if (!preg_match(
                '/^"(\\\\"|[^"])+"$/',
                str_replace("\\\\", "", $local)
            )) {
                $valid = false;
            }
        }

        return $valid;
    }
}
