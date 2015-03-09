<?php
namespace FwlibTest\Aide;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface FunctionMockWrapperInterface
{
    /**
     * @see FunctionMockBuilderTrait::buildFunctionMock()
     *
     * @param   string      $namespace
     * @return  static
     */
    public function build($namespace);


    /**
     * @see Mock::disable()
     * @return  static
     */
    public function disable();


    /**
     * @see Mock::disableAll()
     * @return  static
     */
    public function disableAll();


    /**
     * @see Mock::enable()
     * @return  static
     */
    public function enable();


    /**
     * Getter of $result
     *
     * @return  mixed
     */
    public function getResult();


    /**
     * Setter of $result
     *
     * @param   mixed   $result
     * @return  static
     */
    public function setResult($result);
}
