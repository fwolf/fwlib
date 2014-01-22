<?php
namespace Fwlib\Bridge;


/**
 * Send mail using PHPMailer
 *
 * Extend from official PHPMailer, make it easier to use with some helper
 * method, default through SMTP.
 *
 * @copyright   Copyright 2007-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2007-03-29
 */
class PHPMailer extends \PHPMailer
{
    /**
     * Charset of mail, overwrite parent default value
     *
     * @var string
     */
    public $CharSet = 'utf-8';

    /**
     * Encoding method of mail body, overwrite parent default value
     *
     * @var string
     */
    public $Encoding = 'base64';

    /**
     * Error count, reset when mail send success
     *
     * @var int
     */
    public $errorCount = 0;

    /**
     * Error message, reset when mail send success
     *
     * @var string
     */
    public $errorMsg = '';

    /**
     * Mail from address, overwrite parent default value
     *
     * @var string
     */
    public $From = '';

    /**
     * Mail from name, overwrite parent default value
     *
     * @var string
     */
    public $FromName = 'Alien';

    /**
     * SMTP host, overwrite parent default value
     *
     * @var string
     */
    public $Host = '';

    /**
     * Which method to use to send mail, overwrite parent default value
     *
     * @var string
     */
    public $Mailer = 'smtp';

    /**
     * Whether to use SMTP authentication, overwrite parent default value
     *
     * @var boolean
     */
    public $SMTPAuth = true;


    /**
     * Parse mail to address
     *
     * Parse string including email name and address to address=>name array.
     *
     * @var string  $address
     * @return  array
     */
    public function parseAddress($address)
    {
        // First, find all mail address
        $j = preg_match_all(
            '/[\s<]?([\w\d\-_\.\+]+@([\w\d\-_]+\.){1,4}\w+)[\s>]?/',
            $address,
            $addressPart
        );

        // If got addresses, find names
        if (0 < $j) {
            $addressAr = array();
            $addressPart = $addressPart[1];

            for ($i = 0; $i < $j; $i++) {
                // Find from start of address string
                $k = strpos($address, $addressPart[$i]);
                $name = substr($address, 0, $k);

                // Prepare for next loop
                $address = substr($address, $k + strlen($addressPart[$i]));

                // Cleanup name we got
                $name = trim($name, ' \t<>;,"');

                $addressAr[$addressPart[$i]] = $name;
            }
            return($addressAr);

        } else {
            return false;
        }
    }


    /**
     * Send mail
     *
     * @return  boolean
     */
    public function send()
    {
        $sendSuccessful = parent::send();
        if (!$sendSuccessful) {
            $this->errorCount ++;
            $this->errorMsg = $this->ErrorInfo;
        } else {
            $this->errorCount = 0;
            $this->errorMsg = '';
        }

        return $sendSuccessful;
    }


    /**
     * Set host auth information
     *
     * @param   string  $user
     * @param   string  $pass
     * @param   string  $authType
     */
    public function setAuth($user, $pass, $authType = '')
    {
        $this->Username = $user;
        $this->Password = $pass;
        $this->AuthType = $authType;
    }


    /**
     * Set mail body
     *
     * @param   string  $body
     */
    public function setBody($body)
    {
        $this->Body = $body;
    }


    /**
     * Set charset of mail
     *
     * @param   string  $charset
     */
    public function setCharset($charset)
    {
        $this->CharSet = $charset;
    }


    /**
     * Set encoding of mail
     *
     * @param   string  $encoding
     */
    public function setEncoding($encoding)
    {
        $this->Encoding = $encoding;
    }


    /**
     * Set host information
     *
     * @param   string  $host
     * @param   int     $port
     * @param   boolean $isSmtp
     * @param   string  $smtpSecure
     */
    public function setHost($host, $port = 25, $isSmtp = true, $smtpSecure = '')
    {
        $this->Host = $host;
        $this->Port = $port;
        $this->SMTPAuth = $isSmtp;
        $this->SMTPSecure = $smtpSecure;
    }


    /**
     * Set mail subject
     *
     * @param   string  $subject
     */
    public function setSubject($subject)
    {
        $this->Subject = $subject;
    }


    /**
     * Set address to mail to
     *
     * @param   string  $to
     */
    public function setTo($to)
    {
        $this->ClearAddresses();

        foreach ($this->parseAddress($to) as $key => $val) {
            $this->AddAddress($key, $val);
        }
    }
}
