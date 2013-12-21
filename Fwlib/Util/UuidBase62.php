<?php
namespace Fwlib\Util;

use Fwlib\Util\AbstractUtilAware;

/**
 * UUID generator using base-62 character (0-9a-zA-Z)
 *
 * UUID format:
 *
 * [second][microsecond][group][custom][random]
 *
 * second: 6 chars, seconds in microtime.
 *      base_convert('ZZZZZZ', 62, 10) = 56800235583 = 3769-12-05 11:13:03
 * microsecond: 4 chars, micro-second in microtime, plus 1000000.
 *      base_convert(999999, 10, 62) = 4c91
 * group: 2 chars, user defined, 'a0' if empty.
 *      base_convert('zz', 62, 10) = 3843, enough to group server.
 * custom: 6 chars, user defined, convert from user ip if empty,
 *      and random if user ip cannot get.
 *      base_convert(ip2long('255.255.255.255'), 10, 62) = 4GFfc3
 *      ipv6 can use head or tail part.
 * random: 6 chars, random string.
 *      62^6 = 56800235584, about 13x of 16^8 = 4294967296,
 *      and microsecond is 100x of general UUID.
 * (Notice: base_convert() doesn't allow base greater than 36.)
 *
 * Length of UUID is 24 bytes, no separator.
 *
 * Notice: Mix of a-zA-Z may not suit for Mysql UUID, because Mysql default
 * compare string CASE INSENSITIVE.
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-10-31
 */
class UuidBase62 extends AbstractUtilAware
{
    /**
     * Number base
     */
    protected $base = 62;

    /**
     * UUID length
     */
    protected $length = 24;

    /**
     * Length of custom part
     */
    protected $lengthCustom = 6;

    /**
     * Length of group part
     */
    protected $lengthGroup = 2;

    /**
     * Length of random part
     */
    protected $lengthRandom = 6;

    /**
     * Mode when call StringUtil::random()
     */
    protected $randomMode = 'aA0';


    /**
     * Add check digit to an Uuid
     *
     * Check digit will replace last byte.
     *
     * @param   string  $uuid
     */
    public function addCheckDigit($uuid)
    {
        $uuid = substr($uuid, 0, $this->length - 1);
        $uuid .= strtolower(
            $this->getUtil('Iso7064')->encode($uuid, '3736', false)
        );

        return $uuid;
    }


    /**
     * Generate an UUID
     *
     * If $checkDigit is true, use last byte as check digit,
     * by ISO 7064 Mod 17,16 algorithm.
     *
     * In product envionment, $group should start from a0, 00-09 is reserved
     * for develop/test.
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

        $numberUtil = $this->getUtil('NumberUtil');
        $httpUtil = $this->getUtil('HttpUtil');
        $stringUtil = $this->getUtil('StringUtil');

        // Seconds from now(Nov 2013) will fill length 6
        $uuid = $numberUtil->baseConvert($sec, 10, $this->base);
        // Microsends will fill to length 4
        $usec = $numberUtil->baseConvert(round($usec * 1000000), 10, $this->base);
        $uuid .= str_pad($usec, 4, '0', STR_PAD_LEFT);


        if (empty($group) || $this->lengthGroup > strlen($group)) {
            $group = str_pad((string)$group, $this->lengthGroup, '0', STR_PAD_LEFT);
        } else {
            $group = substr($group, -1 * $this->lengthGroup);
        }
        $uuid .= $group;


        if (empty($custom)) {
            $custom = $numberUtil->baseConvert(
                sprintf('%u', ip2long($httpUtil->getClientIp())),
                10,
                $this->base
            );
        }
        if ($this->lengthCustom != strlen($custom)) {
            $custom = $stringUtil->random(
                $this->lengthCustom,
                $this->randomMode
            ) . (string)$custom;
            $custom = substr($custom, -1 * $this->lengthCustom);
        }
        $uuid .= $custom;

        $uuid .= $stringUtil->random($this->lengthRandom, $this->randomMode);


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
        $numberUtil = $this->getUtil('NumberUtil');

        if ($this->length == strlen($uuid)) {
            $sec = $numberUtil->baseConvert(substr($uuid, 0, 6), $this->base, 10);
            $usec = $numberUtil->baseConvert(substr($uuid, 6, 4), $this->base, 10);
            $custom = substr($uuid, 10 + $this->lengthGroup, $this->lengthCustom);
            $random = substr($uuid, -1 * $this->lengthRandom);
            return array(
                'second' => $sec,
                'microsecond' => $usec,
                'time'    => date('Y-m-d H:i:s', $sec),
                'group' => substr($uuid, 10, $this->lengthGroup),
                'custom' => $custom,
                'ip'      => long2ip(
                    $numberUtil->baseConvert($custom, $this->base, 10)
                ),
                'random'  => $random,
            );
        } else {
            return null;
        }
    }


    /**
     * Verify Uuid
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
            array('0', 'a', 'A'),
            array('0-9', 'a-z', 'A-Z'),
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
