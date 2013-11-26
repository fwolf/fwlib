<?php
namespace Fwlib\Bridge\Test;


/**
 * Dummy for test Fwlib\Bridge\PHPUnitTestCase
 *
 * @package     Fwlib\Bridge\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-26
 */
class PHPUnitTestCaseDummy
{
    private $privateProperty = 42;


    protected function protectedMethod($x, $y)
    {
        return intval($x . $y);
    }
}
