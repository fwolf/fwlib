<?php
namespace FwlibTest\Aide;

/**
 * Trait for function mock client
 *
 * Function mock can get from factory, or use quick getter.
 *
 * @see \Fwlib\Base\ServiceContainerAwareTrait
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FunctionMockAwareTrait
{
    /**
     * @var FunctionMockFactory
     */
    protected $functionMockFactory = null;


    /**
     * Quick getter of function mock
     *
     * Mock is default enabled.
     *
     * Default namespace is corresponding to test class namespace, with 'Test'
     * suffix removed from first section of namespace.
     *
     * @param   string  $function
     * @param   bool    $enabled
     * @param   string  $namespace
     * @return  FunctionMockWrapperInterface
     */
    protected function getFunctionMock(
        $function,
        $enabled = true,
        $namespace = null
    ) {
        if (is_null($namespace)) {
            $namespace = get_class($this);
            $namespace = implode(
                '\\',
                array_slice(explode('\\', $namespace), 0, -1)
            );

            $namespace = preg_replace('/^(\S+)Test\\\/', '$1\\', $namespace);
        }

        $factory = $this->getFunctionMockFactory($namespace);

        return $factory->get(null, $function, $enabled);
    }


    /**
     * @param   string  $namespace  Namespace or any full class name in it
     * @return  FunctionMockFactory
     */
    protected function getFunctionMockFactory($namespace = null)
    {
        $factory = is_null($this->functionMockFactory)
            ? FunctionMockFactory::getInstance()
            : $this->functionMockFactory;

        if (!empty($namespace)) {
            $factory->setNamespace($namespace);
        }

        return $factory;
    }


    /**
     * @param   FunctionMockFactory $functionMockFactory
     * @return  static
     */
    public function setFunctionMockFactory(
        FunctionMockFactory $functionMockFactory
    ) {
        $this->functionMockFactory = $functionMockFactory;

        return $this;
    }
}
