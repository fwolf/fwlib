<?php
namespace Fwlib\Util\Uuid;

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
 * (Notice: base_convert() does not allow base greater than 36.)
 *
 * Length of UUID is 24 bytes, no separator.
 *
 * Notice: Mix of a-zA-Z may not suit for Mysql UUID, because Mysql default
 * compare string CASE INSENSITIVE.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base62 implements GeneratorInterface
{
    use TimeBasedGeneratorTrait;


    /** @var int */
    protected $base = 62;

    /** @var string */
    protected $checkDigitMode = '3736';

    /** @var int */
    protected $length = 24;

    /** @var int */
    protected $lengthOfCustom = 6;

    /** @var int */
    protected $lengthOfGroup = 2;

    /** @var int */
    protected $lengthOfRandom = 6;

    /** @var string */
    protected $randomMode = 'aA0';
}
