<?php
namespace Fwlib\Util\Uuid;

/**
 * UUID generator using base-36 character, short version
 *
 * {@see Base36} algorithm is good, but the uuid is too long. For small scale
 * system, we may need this short version.
 *
 * The second and micro second part remain same length and algorithm, to keep
 * maximum usage of PHP microtime precision, so the lifetime and timestamp
 * offset are also same.
 *
 * As a small scale, we reduce length of group to 1, maximum 36 servers.
 *
 * The rest parts must ensure no duplicate in 0.000001 second, we use decimal
 * part of {@see uniqid()} with more_entropy enabled. as its integer part is
 * generated base on microtime. We got a 8 digit number, enough to avoid dup in
 * 1 microsecond. The largest number 99,999,999 in base36 is '1n....' cost 6
 * bytes, so we divide it by 2 to use 5 bytes storage, should be enough too.
 *
 * Length of UUID is 16 bytes, no separator.
 *
 *
 * For safe speed in 1 microsecond (not millisecond), reference:
 * @see http://github.com/mumrah/flake-java   1k / millisecond = 1/ms
 * @see https://github.com/twitter/snowflake/tree/snowflake-2010    0.5/ms
 *
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Base36Short extends Base36
{
    /** @var int */
    protected $length = 16;

    /** @var int */
    protected $lengthOfGroup = 1;

    /** @var int */
    protected $lengthOfRandom = 5;


    /**
     * {@inheritdoc}
     *
     * Default group id changes to 1, leave 0 to test server.
     *
     * Copy from {@see TimeBasedGeneratorTrait} avoid overwritten of
     * generateCustom() , should give a little performance bonus.
     */
    public function generate(
        $groupId = '1',
        $custom = '',
        $checkDigit = false
    ) {
        $uuid = $this->generateTime() .
            $this->generateGroup($groupId) .
            $this->generateRandom();

        if ($checkDigit) {
            $uuid = $this->addCheckDigit($uuid, true);
        }

        return $uuid;
    }


    /**
     * {@inheritdoc}
     */
    protected function generateRandom()
    {
        $decimalPart = substr(uniqid('', true), -8);

        $decimalPart = round($decimalPart / 2);

        $encoded = base_convert($decimalPart, 10, 36);

        $random = str_pad($encoded, $this->lengthOfRandom, '0', STR_PAD_LEFT);

        return $random;
    }
}
