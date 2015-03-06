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
     * Convert number base
     *
     * @param   string|int  $source
     * @param   int         $fromBase
     * @param   int         $toBase
     * @return  string
     */
    protected function convertBase($source, $fromBase, $toBase)
    {
        if (36 >= $fromBase && 36 >= $toBase) {
            $result = base_convert($source, $fromBase, $toBase);

        } else {
            $numberUtil = $this->getUtilContainer()->getNumber();
            $result = $numberUtil->baseConvert($source, $fromBase, $toBase);
        }

        return $result;
    }


    /**
     * @see GeneratorInterface::generate()
     *
     * @param   int|string $groupId
     * @param   string     $custom
     * @param   boolean    $checkDigit
     * @return  string
     */
    public function generate(
        $groupId = '10',
        $custom = '',
        $checkDigit = false
    ) {
        $uuid = $this->generateTime() .
            $this->generateGroup($groupId) .
            $this->generateCustom($custom) .
            $this->generateRandom();

        if ($checkDigit) {
            $uuid = $this->addCheckDigit($uuid, true);
        }

        return $uuid;
    }


    /**
     * Generate custom part
     *
     * If given string exceed length limit, leading part will be trimmed.
     *
     * @param   string  $custom
     * @return  string
     */
    protected function generateCustom($custom)
    {
        if (empty($custom)) {
            $httpUtil = $this->getUtilContainer()->getHttp();

            $custom = $this->convertBase(
                sprintf('%u', ip2long($httpUtil->getClientIp())),
                10,
                $this->base
            );
        }

        $lengthDiff = $this->lengthOfCustom - strlen($custom);
        if (0 < $lengthDiff) {
            $stringUtil = $this->getUtilContainer()->getString();

            $custom = $stringUtil->random($lengthDiff, $this->randomMode) .
                $custom;

        } elseif (0 > $lengthDiff) {
            $custom = substr($custom, -1 * $this->lengthOfCustom);
        }

        return $custom;
    }


    /**
     * Generate group part
     *
     * If given group exceed length of group, the leading part will be trimmed.
     *
     * @param   int|string  $groupId
     * @return  string
     */
    protected function generateGroup($groupId)
    {
        $groupId = strval($groupId);
        $length = $this->lengthOfGroup;

        if (empty($groupId) || $length > strlen($groupId)) {
            $groupId = str_pad($groupId, $length, '0', STR_PAD_LEFT);

        } else {
            $groupId = substr($groupId, -1 * $length);
        }

        return $groupId;
    }


    /**
     * @param   float   $microSecond
     * @return  string  Length: 4
     */
    protected function generateMicroSecond($microSecond)
    {
        $second = round($microSecond * 1000000);

        $result = $this->convertBase($second, 10, $this->base);

        $result = str_pad($result, 4, '0', STR_PAD_LEFT);

        return $result;
    }


    /**
     * @return  string
     */
    protected function generateRandom()
    {
        $stringUtil = $this->getUtilContainer()->getString();

        return $stringUtil->random($this->lengthOfRandom, $this->randomMode);
    }


    /**
     * @param   int     $second
     * @return  string  Timestamp to base 36 is 6 digit since Nov 2013.
     */
    protected function generateSecond($second)
    {
        return $this->convertBase($second, 10, $this->base);
    }


    /**
     * Generate time part, include second and microsecond
     *
     * @return  string
     */
    protected function generateTime()
    {
        list($microSecond, $second) = explode(' ', microtime());

        return $this->generateSecond($second) .
            $this->generateMicroSecond($microSecond);
    }


    /**
     * Parse uuid, see what it means
     *
     * @param   string  $uuid
     * @return  array
     */
    public function parse($uuid)
    {
        if ($this->length == strlen($uuid)) {
            $sec = $this->convertBase(substr($uuid, 0, 6), $this->base, 10);
            $usec = $this->convertBase(substr($uuid, 6, 4), $this->base, 10);
            $custom = substr($uuid, 10 + $this->lengthOfGroup, $this->lengthOfCustom);
            $random = substr($uuid, -1 * $this->lengthOfRandom);
            return [
                'second' => $sec,
                'microsecond' => $usec,
                'time'    => date('Y-m-d H:i:s', $sec),
                'group' => substr($uuid, 10, $this->lengthOfGroup),
                'custom' => $custom,
                'ip'      => long2ip(
                    $this->convertBase($custom, $this->base, 10)
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
