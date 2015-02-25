<?php
namespace Fwlib\Bridge;

/**
 * Bridge for PHPUnit_Framework_TestCase
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
     * @param   mixed   $classOrInstance
     * @param   string  $name
     * @param   array   $argument
     */
    protected function reflectionCall(
        $classOrInstance,
        $name,
        array $argument = []
    ) {
        $ref = new \ReflectionMethod($classOrInstance, $name);

        $ref->setAccessible(true);

        return $ref->invokeArgs($classOrInstance, $argument);
    }


    /**
     * Get private or protected property for test using reflection
     *
     * @param   mixed   $classOrInstance
     * @param   string  $name
     * @return  mixed
     */
    protected function reflectionGet($classOrInstance, $name)
    {
        $ref = new \ReflectionProperty($classOrInstance, $name);

        $ref->setAccessible(true);

        return $ref->getValue($classOrInstance);
    }


    /**
     * Set private or protected property for test using reflection
     *
     * @param   mixed   $classOrInstance
     * @param   string  $name
     * @param   mixed   $value
     */
    protected function reflectionSet($classOrInstance, $name, $value)
    {
        $ref = new \ReflectionProperty($classOrInstance, $name);

        $ref->setAccessible(true);

        if ($ref->isStatic()) {
            $ref->setValue($value);
        } else {
            $ref->setValue($classOrInstance, $value);
        }
    }
}
