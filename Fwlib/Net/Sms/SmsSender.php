<?php
namespace Fwlib\Net\Sms;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Net\Sms\SmsLogger;

/**
 * SMS Sender
 *
 * Supported SMS send method:
 * - gammu smsd inject command
 *
 * @package     Fwlib\Net\Sms
 * @copyright   Copyright 2010-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-11-23
 */
class SmsSender extends AbstractAutoNewConfig
{
    /**
     * SMS logger object
     *
     * @var Fwlib\Net\Sms\SmsLogger
     */
    protected $smsLogger = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = null)
    {
        unset($this->smsLogger);

        parent::__construct();
    }


    /**
     * Find path of gammu smsd inject cmd
     *
     * If find in $path fail, will try path in $this->config['path.bin'].  The
     * exe filename is hardcoded 'gammu-smsd-inject'.
     *
     * @param   $path   Manual additional path
     * @return  mixed   Path of inject cmd, false when fail.
     */
    public function getPathOfGammuSmsdInject($path = '')
    {
        if (!empty($this->config['path.gammuSmsdInject'])) {
            return $this->config['path.gammuSmsdInject'];
        }


        $arPath = $this->config['path.bin'];
        if (!empty($path)) {
            array_unshift($arPath, $path);
        }

        // Find a usable path
        $found = false;
        while (!$found && !empty($arPath)) {
            $cmd = array_shift($arPath) . 'gammu-smsd-inject';
            if (is_executable($cmd)) {
                $found = true;
                break;
            }
        }

        if ($found) {
            return $cmd;
        } else {
            return false;
        }
    }


    /**
     * New SMS logger object
     *
     * @return  Fwlib\Net\Sms\SmsLogger
     */
    protected function newObjSmsLogger()
    {
        // One-time use object, not using ServiceContainer
        return new SmsLogger($this->serviceContainer->get('Db'));
    }


    /**
     * Parse phone number string
     *
     * Do:
     *  Split phone number,
     *  Format phone number,
     *  Remove duplicate number.
     *
     * Only support mobile number of china mainland (start with +86 or 0086).
     *
     * @param   array|string    $number
     * @return  array
     */
    public function parsePhoneNumber($number)
    {
        // If array given, still need convert to string,  for format and
        // validate later.
        if (is_array($number)) {
            $number = implode(',', $number);
        }

        // Remove special chars
        $number = str_replace(array('，', '。', '；'), ',', $number);
        $number = preg_replace('/[ ,;\r\n\t]{1,}/', ',', $number);
        $arNumber = explode(',', $number);

        // Format and remove invalid number
        foreach ($arNumber as $k => &$n) {
            // Remove +86, 0086
            if ('+86' == substr($n, 0, 3)) {
                $n = substr($n, 3);
            }
            if ('0086' == substr($n, 0, 4)) {
                $n = substr($n, 4);
            }

            // Invalid length or not special service number
            if (11 != strlen($n) && '10' != substr($n, 0, 2)) {
                unset($arNumber[$k]);
            }
        }
        unset($n);

        // Remove duplicate
        $arNumber = array_unique($arNumber);

        // Resort array index
        $arNumber = array_merge($arNumber, array());

        return $arNumber;
    }


    /**
     * Send SMS
     *
     * @param   mixed   $destNumber
     * @param   string  $sms
     * @param   integer $cat
     * @return  integer             Actual valid phone number sent.
     */
    public function send($destNumber, $sms, $cat = 0)
    {
        // Map of method config to send function
        $map = array(
            'gammuSmsdInject'   => 'sendUsingGammuSmsdInject',
        );


        $destNumber = $this->parsePhoneNumber($destNumber);
        if (1 > count($destNumber)) {
            throw new \Exception('No valid number to sent.');
        }


        $method = $this->config['method'];
        if (isset($map[$method])) {
            $func = $map[$this->config['method']];
            $i = $this->$func($destNumber, $sms);

            $this->smsLogger->log($destNumber, $sms, $cat);

            return $i;

        } else {
            throw new \Exception("Method $method not supported.");
        }
    }


    /**
     * Send SMS using gammu smsd inject method
     *
     * Notice: On webserver, need assign user www-data to gammu group, and
     * make /var/log/gammu-smsd.log g+w.
     *
     * Modem server need not, only conn to db is required.
     *
     * $destNumber may be array of phone number, or string of numbers splitted
     * by any char of " ,;，；。\r\n".
     *
     * @param   mixed   $destNumber
     * @param   string  $sms
     * @return  integer             Actual valid phone number sent.
     */
    protected function sendUsingGammuSmsdInject($destNumber, $sms)
    {
        $injectCmd = $this->getPathOfGammuSmsdInject();
        if (empty($injectCmd)) {
            throw new \Exception(
                'Can\'t find gammu smsd inject execute file.'
            );
        }

        // Prepare cmd to sent
        $cmd = str_replace(
            array('[cmd]', '[sms]'),
            array($this->config['path.gammuSmsdInject'], addslashes($sms)),
            $this->config['cmd.gammuSmsdInject']
        );
        $i = strpos($cmd, '[dest]');
        if (1 > $i) {
            throw new \Exception(
                'Command template of gammu smsd inject error.'
            );
        }
        $cmd1 = substr($cmd, 0, $i);
        $cmd2 = substr($cmd, $i + 6);   // 6 is length of '[dest]'

        // Loop to sent each number
        foreach ($destNumber as $dest) {
            $cmd = $cmd1 . $dest . $cmd2;
            $output = array();
            $returnValue = 0;
            exec($cmd, $output, $returnValue);

            if (0 != $returnValue) {
                throw new \Exception('Gammu inject error: ' . $output[1]);
            }
        }

        return count($destNumber);
    }


    /**
     * Set default config
     */
    public function setConfigDefault()
    {
        // SMS send method
        $this->config['method'] = 'gammuSmsdInject';

        // Possible bin path
        $this->config['path.bin'] = array(
            '/usr/bin/',
            '/usr/local/bin/',
            '/bin/',
        );

        // Path of gammu-smsd-inject, leave empty to find in path.bin.
        // Set this will bypass inject cmd search in path.bin.
        $this->config['path.gammuSmsdInject'] = '';

        // Cmd template of gammu-smsd-inject cmd
        $this->config['cmd.gammuSmsdInject']
            = '[cmd] TEXT [dest] -autolen 600 -report -validity MAX -unicode -textutf8 "[sms]"';
    }
}
