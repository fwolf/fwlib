<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMock\Helper\DirectReturnTrait;
use FwlibTest\Aide\FunctionMockWrapperInterface;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FilterInputArray implements FunctionMockWrapperInterface
{
    use DirectReturnTrait;


    /** @type string|int|bool */
    public $function = 'filter_input_array';
}
