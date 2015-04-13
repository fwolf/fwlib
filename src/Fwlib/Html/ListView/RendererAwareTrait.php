<?php
namespace Fwlib\Html\ListView;

/**
 * RendererAwareTrait
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RendererAwareTrait
{
    /**
     * @var RendererInterface
     */
    protected $renderer = null;


    /**
     * @return  RendererInterface
     */
    protected function getRenderer()
    {
        return $this->renderer;
    }


    /**
     * @param   RendererInterface $renderer
     * @return  static
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }
}
