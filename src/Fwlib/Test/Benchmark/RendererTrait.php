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
     * @see Benchmark::$groups
     *
     * @var array
     */
    protected $groups = [];

    /**
     * @see Benchmark::$markers
     *
     * @var array
     */
    protected $markers = [];


    /**
     * @see RendererInterface::setGroups()
     * @see Benchmark::$groups
     *
     * @param   array   $groups
     * @return  static
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;

        return $this;
    }


    /**
     * @see RendererInterface::setMarkers()
     * @see Benchmark::$markers
     *
     * @param   array   $markers
     * @return  static
     */
    public function setMarkers(array $markers)
    {
        $this->markers = $markers;

        return $this;
    }
}
