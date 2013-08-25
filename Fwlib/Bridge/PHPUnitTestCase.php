<?php
namespace Fwlib\Bridge;


/**
 * Bridge for PHPUnit_Framework_TestCase
 *
 * @package     Fwlib\Bridge
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-25
 */
abstract class PHPUnitTestCase extends \PHPunit_Framework_TestCase
{
    public function assertEqualArray(
        $expected,
        $actual,
        $message = '',
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        self::assertEquals(
            var_export($expected, true),
            var_export($actual, true),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }
}
