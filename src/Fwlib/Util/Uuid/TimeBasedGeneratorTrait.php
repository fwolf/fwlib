<?php
namespace Fwlib\Util\Uuid;

use Fwlib\Util\UtilContainerAwareTrait;

/**
 * UUID generator using converted date as header
 *
 * Using time(timestamp with microsecond actually) as uuid header, the result
 * is sorted by time, good for db storage and avoid conflict.
 *
 * Usually time will be convert from number to alphabet and number mixed style
 * to reduce length.
 *
 *
 * Notice: configure properties are not defined in trait, they must be filled
 * in client classes.
 *
 * @property int    $base             Number base when do convert
 * @property string $checkDigitMode   Mode of check digit generate algorithm.
 *                                    Eg: '3736' or '1716' of Iso7064.
 * @property int    $length           UUID total length
 * @property int    $lengthOfCustom   Length of custom part
 * @property int    $lengthOfGroup    Length of group part
 * @property int    $lengthOfRandom   Length of random part
 * @property string $randomMode       String combine of 'a' 'A' '0', means
 *                                    lower/upper cased alphabet or number,
 *                                    will be used when generate random string.
 *
 * @see GeneratorInterface
 * @see Base16              Test through Base16 test case.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait TimeBasedGeneratorTrait
{
    use UtilContainerAwareTrait;


    /**
     * Add check digit to an Uuid
     *
     * Check digit will replace last byte, always lower cased.
     *
     * @param   string  $uuid
     * @return  string
     */
    public function addCheckDigit($uuid)
    {
        $uuid = substr($uuid, 0, $this->length - 1);
        $uuid .= strtolower(
            $this->getUtilContainer()->getIso7064()
                ->encode($uuid, $this->checkDigitMode, false)
        );

        return $uuid;
    }


    /**
     * @see GeneratorInterface::generate()
     *
     * @param   string  $group
     * @param   string  $custom
     * @param   boolean $checkDigit
     * @return  string
     */
    public function generate(
        $group = '10',
        $custom = '',
        $checkDigit = false
    ) {
        list($usec, $sec) = explode(' ', microtime());

        $numberUtil = $this->getUtilContainer()->getNumber();
        $httpUtil = $this->getUtilContainer()->getHttp();
        $stringUtil = $this->getUtilContainer()->getString();

        // Seconds from now(Nov 2013) will fill length 6
        $uuid = $numberUtil->baseConvert($sec, 10, $this->base);
        // Microseconds will fill to length 4
        $usec = $numberUtil->baseConvert(round($usec * 1000000), 10, $this->base);
        $uuid .= str_pad($usec, 4, '0', STR_PAD_LEFT);


        if (empty($group) || $this->lengthOfGroup > strlen($group)) {
            $group = str_pad((string)$group, $this->lengthOfGroup, '0', STR_PAD_LEFT);
        } else {
            $group = substr($group, -1 * $this->lengthOfGroup);
        }
        $uuid .= $group;


        if (empty($custom)) {
            $custom = $numberUtil->baseConvert(
                sprintf('%u', ip2long($httpUtil->getClientIp())),
                10,
                $this->base
            );
        }
        if ($this->lengthOfCustom != strlen($custom)) {
            $custom = $stringUtil->random(
                $this->lengthOfCustom,
                $this->randomMode
            ) . (string)$custom;
            $custom = substr($custom, -1 * $this->lengthOfCustom);
        }
        $uuid .= $custom;

        $uuid .= $stringUtil->random($this->lengthOfRandom, $this->randomMode);


        if ($checkDigit) {
            $uuid = $this->addCheckDigit($uuid, true);
        }

        return $uuid;
    }


    /**
     * Parse uuid, see what it means
     *
     * @param   string  $uuid
     * @return  array
     */
    public function parse($uuid)
    {
        $numberUtil = $this->getUtilContainer()->getNumber();

        if ($this->length == strlen($uuid)) {
            $sec = $numberUtil->baseConvert(substr($uuid, 0, 6), $this->base, 10);
            $usec = $numberUtil->baseConvert(substr($uuid, 6, 4), $this->base, 10);
            $custom = substr($uuid, 10 + $this->lengthOfGroup, $this->lengthOfCustom);
            $random = substr($uuid, -1 * $this->lengthOfRandom);
            return [
                'second' => $sec,
                'microsecond' => $usec,
                'time'    => date('Y-m-d H:i:s', $sec),
                'group' => substr($uuid, 10, $this->lengthOfGroup),
                'custom' => $custom,
                'ip'      => long2ip(
                    $numberUtil->baseConvert($custom, $this->base, 10)
                ),
                'random'  => $random,
            ];
        } else {
            return null;
        }
    }


    /**
     * @see GeneratorInterface::verify()
     *
     * @param   string  $uuid
     * @param   boolean $withCheckDigit     Source includes check digit
     * @return  boolean
     */
    public function verify($uuid, $withCheckDigit = false)
    {
        if ($this->length != strlen($uuid)) {
            return false;
        }

        // AlphaNumeric 0-9 a-z A-Z
        $chars = str_replace(
            ['0', 'a', 'A'],
            ['0-9', 'a-z', 'A-Z'],
            $this->randomMode
        );
        if ('' !== preg_replace("/[$chars]/", '', $uuid)) {
            return false;
        }

        // Check digit
        if ($withCheckDigit && ($uuid != $this->addCheckDigit($uuid))) {
            return false;
        }

        return true;
    }
}
