<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMock\Helper\DirectReturnTrait;
use FwlibTest\Aide\FunctionMockWrapperInterface;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Checkdnsrr implements FunctionMockWrapperInterface
{
    use DirectReturnTrait;


    /** @type string */
    public $function = 'checkdnsrr';
}
