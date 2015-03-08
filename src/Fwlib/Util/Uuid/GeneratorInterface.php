<?php
namespace Fwlib\Util\Uuid;

/**
 * UUID generator
 *
 * UUID are combine of several parts:
 *
 * [second][microsecond][group][custom][random]
 *
 * Length of each part are different.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface GeneratorInterface
{
    /**
     * Generate an UUID
     *
     * If $checkDigit is true, use last byte as check digit.
     *
     * Normally group below 10(what ever base) should reserve for develop/test
     * environment.
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
    );


    /**
     * Verify Uuid
     *
     * @param   string  $uuid
     * @param   boolean $withCheckDigit Source includes check digit
     * @return  boolean
     */
    public function verify($uuid, $withCheckDigit = false);
}
