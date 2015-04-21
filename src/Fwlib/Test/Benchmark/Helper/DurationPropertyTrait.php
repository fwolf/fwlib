<?php
namespace Fwlib\Test\Benchmark\Helper;

/**
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait DurationPropertyTrait
{
    /**
     * Time duration from previous marker, by microsecond
     *
     * @var float
     */
    protected $duration = null;


    /**
     * Getter of $duration
     *
     * @return  float
     */
    public function getDuration()
    {
        return $this->duration;
    }


    /**
     * Setter of $duration
     *
     * @param   float $duration
     * @return  static
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }
}
