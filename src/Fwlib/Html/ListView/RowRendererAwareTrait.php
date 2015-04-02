<?php
namespace Fwlib\Html\ListView;

/**
 * RowRendererAwareTrait
 *
 * Row renderer is callback, not a class instance, it is used in
 * {@see Renderer} and can also be injected from {@see ListView}.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RowRendererAwareTrait
{
    /**
     * @var callable
     */
    protected $rowRenderer = null;


    /**
     * @return  callable
     */
    protected function getRowRenderer()
    {
        return $this->rowRenderer;
    }


    /**
     * @param   callable $rowRenderer
     * @return  static
     */
    public function setRowRenderer(callable $rowRenderer)
    {
        $this->rowRenderer = $rowRenderer;

        return $this;
    }
}
