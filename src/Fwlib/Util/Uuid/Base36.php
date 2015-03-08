<?php
namespace Fwlib\Util\Uuid;

/**
 * UUID generator using base-36 character (0-9a-z)
 *
 * UUID format:
 *
 * [second][microsecond][group][custom][random]
 *
 * second: 6 chars, seconds in microtime.
 *      base_convert('zzzzzz', 36, 10) = 2176782335 = 2038-12-24 13:45:35
 * microsecond: 4 chars, micro-second in microtime, plus 1000000.
 *      base_convert(999999, 10, 36) = lflr
 * group: 2 chars, user defined, 'a0' if empty.
 *      base_convert('zz', 36, 10) = 1295, enough to group server.
 * custom: 7 chars, user defined, convert from user ip if empty,
 *      and random if user ip cannot get.
 *      base_convert(ip2long('255.255.255.255'), 10, 36) = 1z141z3
 *      ipv6 can use head or tail part.
 * random: 6 chars, random string.
 *      36^6 = 2176782336, about 50% of 16^8 = 4294967296,
 *      but microsecond is 100x of general UUID.
 *
 * Length of UUID is 25 bytes, no separator.
 *
 *
 * Change: For wider time range than 2038, we can start count timestamp from
 * not year 1970. For more, compute second and microsecond together to use
 * lflr~z{4} part. After this, the usable years raised to about 110 years.
 *      Offset time: 2012-07-11, 1341957600
 *      Lifetime start: base36 of 10{6 + 4 - 1} / 10^6 + offset timestamp =
 *          1443517557 = 2015-09-29 11:05:57
 *      Lifetime end: base36 of z{6 + 4} / 10^6 + offset timestamp =
 *          4998116040 = 2128-05-20 14:34:00
 * Notice in above example: start time is in future of its written time :-).
 *
 * @see http://3v4l.org/YPTHo       Find best start date
 * @see https://gist.github.com/fwolf/5f3e44343a3bebf36953
 * @see http://3v4l.org/FMINm       Estimate lifetime
 * @see https://gist.github.com/fwolf/b5b10173b00086d5f33c
 *
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base36 implements GeneratorInterface
{
    use TimeBasedGeneratorTrait;


    /**
     * Start offset of timestamp
     *
     * @var int
     */
    const TIMESTAMP_OFFSET = 1293840000;    // 2011-01-01


    /** @var int */
    protected $base = 36;

    /** @var string */
    protected $checkDigitMode = '3736';

    /** @var int */
    protected $length = 25;

    /** @var int */
    protected $lengthOfCustom = 7;

    /** @var int */
    protected $lengthOfGroup = 2;

    /** @var int */
    protected $lengthOfRandom = 6;

    /** @var string */
    protected $randomMode = 'a0';


    /**
     * {@inheritdoc}
     */
    protected function generateTime()
    {
        list($microSecond, $second) = explode(' ', microtime());

        $microSecond = round($microSecond * 1000000);
        $microSecond = str_pad($microSecond, 6, '0', STR_PAD_LEFT);

        $timestamp = $second - self::TIMESTAMP_OFFSET . $microSecond;

        return $this->convertBase($timestamp, 10, $this->base);
    }
}
