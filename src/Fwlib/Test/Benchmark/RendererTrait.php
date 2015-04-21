<?php
namespace Fwlib\Test\Benchmark;

/**
 * Common implement of RendererInterface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RendererTrait
{
    /**
     * @var Group[]
     */
    protected $groups = [];

    /**
     * @var Marker[]
     */
    protected $markers = [];


    /**
     * @see RendererInterface::setGroups()
     *
     * @param   Group[] $groups
     * @return  static
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;

        return $this;
    }


    /**
     * @see RendererInterface::setMarkers()
     *
     * @param   Marker[]    $markers
     * @return  static
     */
    public function setMarkers(array $markers)
    {
        $this->markers = $markers;

        return $this;
    }
}
