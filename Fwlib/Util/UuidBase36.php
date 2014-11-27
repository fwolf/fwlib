<?php
namespace Fwlib\Util;

use Fwlib\Util\UuidBase62;

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
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class UuidBase36 extends UuidBase62
{
    /**
     * Number base
     */
    protected $base = 36;

    /**
     * UUID length
     */
    protected $length = 25;

    /**
     * Length of custom part
     */
    protected $lengthCustom = 7;

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
    protected $randomMode = 'a0';
}
