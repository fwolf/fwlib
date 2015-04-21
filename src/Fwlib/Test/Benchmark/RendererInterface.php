<?php
namespace Fwlib\Test\Benchmark;

/**
 * RendererInterface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface RendererInterface
{
    /**
     * Getter of output
     *
     * @return  string
     */
    public function getOutput();

    /**
     * Setter of groups
     *
     * @see Benchmark::$groups
     *
     * @param   array   $groups
     * @return  static
     */
    public function setGroups(array $groups);

    /**
     * Setter of markers
     *
     * @see Benchmark::$markers
     *
     * @param   array   $markers
     * @return  static
     */
    public function setMarkers(array $markers);
}
