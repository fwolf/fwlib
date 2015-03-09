<?php
namespace FwlibTest\Aide\Mock;

use Fwlib\Util\UtilContainer;
use malkusch\phpmock\Mock;
use malkusch\phpmock\MockBuilder;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FunctionMockTrait
{
    /**
     * Template of building mock
     *
     * @param   string      $namespace
     * @param   string      $function
     * @param   callable    $callback   Null to return property with same name
     * @return  Mock
     */
    protected function buildFunctionMock(
        $namespace,
        $function,
        callable $callback = null
    ) {
        // Do not want to import UtilContainerAwareTrait for test case
        $stringUtil = UtilContainer::getInstance()->getString();

        $functionName = $stringUtil->toCamelCase($function);
        $mockName = "{$functionName}Mock";

        $mock = $this->$mockName;

        if (is_null($callback)) {
            $callback = function() use ($functionName) {
                return self::$$functionName;
            };
        }

        if (is_null($mock)) {
            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            $this->$mockName = $mock;
        }

        return $mock;
    }
}
