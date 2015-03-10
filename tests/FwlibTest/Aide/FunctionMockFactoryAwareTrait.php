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
     * @return  FunctionMockFactory
     */
    protected function getFunctionMockFactory()
    {
        return is_null($this->functionMockFactory)
            ? FunctionMockFactory::getInstance()
            : $this->functionMockFactory;
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
