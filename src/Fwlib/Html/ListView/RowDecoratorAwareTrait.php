<?php
namespace Fwlib\Html\ListView;

/**
 * RowDecoratorAwareTrait
 *
 * After list body data are retrieved from backend, they may need adjust
 * before display, by decorator. Decorator take single list body row array as
 * parameter, and return modified array. Although html code may be applied to
 * cell value, layout of list structure will not be changed, this is different
 * with renderer, which build list structure.
 *
 * @see Renderer::getListBody()
 *
 *
 * Row decorator is usually closure, for convenience, as every list are
 * different, and implement an interface for that is expensive. Decorator can be
 * injected to {@see ListView}.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RowDecoratorAwareTrait
{
    /**
     * @var callable
     */
    protected $rowDecorator = null;


    /**
     * @return  callable
     */
    protected function getRowDecorator()
    {
        return $this->rowDecorator;
    }


    /**
     * @param   callable $rowDecorator
     * @return  static
     */
    public function setRowDecorator(callable $rowDecorator)
    {
        $this->rowDecorator = $rowDecorator;

        return $this;
    }
}
