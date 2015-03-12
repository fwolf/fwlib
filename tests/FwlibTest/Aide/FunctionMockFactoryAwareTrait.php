<?php
namespace FwlibTest\Aide;

/**
 * Trait for function mock factory client
 *
 * @see \Fwlib\Base\ServiceContainerAwareTrait
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FunctionMockFactoryAwareTrait
{
    /**
     * @var FunctionMockFactory
     */
    protected $functionMockFactory = null;


    /**
     * @param   $namespace
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
    public function setUtilContainer(
        FunctionMockFactory $functionMockFactory
    ) {
        $this->functionMockFactory = $functionMockFactory;

        return $this;
    }
}
