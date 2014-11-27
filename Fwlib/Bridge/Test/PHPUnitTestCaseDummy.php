<?php
namespace Fwlib\Bridge\Test;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PHPUnitTestCaseDummy
{
    private $privateProperty = 42;


    protected function protectedMethod($x, $y)
    {
        return intval($x . $y);
    }


    protected function protectedMethodWithoutParameter()
    {
        return $this->privateProperty;
    }
}
