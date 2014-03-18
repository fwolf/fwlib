<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Validator\AbstractConstraint;
use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerInterface;

/**
 * Constraint Email
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-04
 */
class Email extends AbstractConstraint implements UtilAwareInterface
{
    /**
     * Check email domain through dns
     *
     * @var bool
     */
    public $dnsCheck = false;

    /**
     * {@inheritdoc}
     */
    public $messageTemplate = array(
        'default'   => 'The input should be valid email address'
    );

    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


    /**
     * Get util instance
     *
     * Same with Fwlib\Util\AbstractUtilAware::getUtil()
     *
     * @param   string  $name
     * @return  object  Util instance
     */
    protected function getUtil($name)
    {
        if (is_null($this->utilContainer)) {
            $this->setUtilContainer(null);
        }

        return $this->utilContainer->get($name);
    }


    /**
     * Setter of UtilContainer instance
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  AbstractAutoNewInstance
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        if (is_null($utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        } else {
            $this->utilContainer = $utilContainer;
        }

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

        $valid = true;

        $atIndex = strrpos($value, '@');
        if (false === $atIndex) {
            return false;
        }

        $domain = substr($value, $atIndex + 1);
        $local = substr($value, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);

        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $valid = false;

        } elseif ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $valid = false;

        } elseif ($local[0] == '.' || $local[$localLen-1] == '.') {
            // local part starts or ends with '.'
            $valid = false;

        } elseif (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $valid = false;

        } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $valid = false;

        } elseif (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
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

        // Some network provider will return fake A record if a dns query
        // return fail, usually disp some ads, so we only check MX record.
        if ($valid && $this->dnsCheck &&
            $this->getUtil('Env')->isNixOs() &&
            !checkdnsrr($domain, 'MX')
        ) {
            $valid = false;
        }


        if (!$valid) {
            $this->setMessage('default');
        }

        return $valid;
    }
}
