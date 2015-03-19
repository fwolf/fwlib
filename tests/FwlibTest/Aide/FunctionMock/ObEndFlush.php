<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ObEndFlush implements FunctionMockWrapperInterface
{
    use CheckCalledTrait;


    /** @type string */
    public $function = 'ob_end_flush';
}
