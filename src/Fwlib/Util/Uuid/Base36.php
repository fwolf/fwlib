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
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base36 implements GeneratorInterface
{
    use TimeBasedGeneratorTrait;


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
}
