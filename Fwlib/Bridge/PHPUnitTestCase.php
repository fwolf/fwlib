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
abstract class PHPUnitTestCase extends \PHPUnit_Framework_TestCase
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


    /**
     * Call private or protected method for test using reflection
     *
     * @param   mixed   $class
     * @param   mixed   $name
     * @param   array   $args
     */
    protected function reflectionCall($class, $name, $args)
    {
        $ref = new \ReflectionMethod($class, $name);
        $ref->setAccessible(true);
        return $ref->invokeArgs($class, (array)$args);
    }


    /**
     * Get private or protected property for test using reflection
     *
     * @param   mixed   $class
     * @param   string  $name
     * @return  mixed
     */
    protected function reflectionGet($class, $name)
    {
        $ref = new \ReflectionProperty($class, $name);
        $ref->setAccessible(true);
        return $ref->getValue($class);
    }
}
