<?php
namespace FwlibTest\Aide;

use malkusch\phpmock\Mock;
use malkusch\phpmock\MockBuilder;

/**
 * Wrapper of phpmock Mock
 *
 * Added static instance storage, result accessors.
 *
 * An trait instance for all namespace need to mock, but mock instance and
 * result are separated by namespace.
 *
 * @property    string  $function   Function name to be mocked
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FunctionMockWrapperTrait
{
    /**
     * @var Mock[]  Index by namespace
     */
    protected static $mockInstances = [];

    /**
     * Function return value
     *
     * @var mixed[] Index by namespace
     */
    protected static $results = [];

    /**
     * @var string  Current activate namespace
     */
    protected $namespace = '';


    /**
     * Template of building mock
     *
     * @param   string      $namespace
     * @param   callable    $callback   Null to return property with same name
     * @return  static
     */
    protected function buildFunctionMock(
        $namespace,
        callable $callback = null
    ) {
        $this->namespace = $namespace;

        if (is_null($callback)) {
            $callback = function() use ($namespace) {
                return isset(self::$results[$namespace])
                    ? self::$results[$namespace]
                    : null;
            };
        }

        if (!isset(self::$mockInstances[$namespace])) {
            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($this->function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            self::$mockInstances[$namespace] = $mock;
        }

        return $this;
    }


    /**
     * @see FunctionMockWrapperInterface::disable()
     * @return  static
     */
    public function disable()
    {
        self::$mockInstances[$this->namespace]->disable();

        return $this;
    }


    /**
     * @see FunctionMockWrapperInterface::disableAll()
     * @return  static
     */
    public function disableAll()
    {
        self::$mockInstances[$this->namespace]->disableAll();

        return $this;
    }


    /**
     * @see FunctionMockWrapperInterface::enable()
     * @return  static
     */
    public function enable()
    {
        self::$mockInstances[$this->namespace]->enable();

        return $this;
    }


    /**
     * Getter of $result
     *
     * @see FunctionMockWrapperInterface::getResult()
     * @return  mixed
     */
    public function getResult()
    {
        return self::$results[$this->namespace];
    }


    /**
     * Setter of $result
     *
     * @see FunctionMockWrapperInterface::setResult()
     * @param   mixed   $result
     * @return  static
     */
    public function setResult($result)
    {
        self::$results[$this->namespace] = $result;

        return $this;
    }
}
