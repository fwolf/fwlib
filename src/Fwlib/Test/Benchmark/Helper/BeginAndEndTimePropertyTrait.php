<?php
namespace Fwlib\Test\Benchmark\Helper;

/**
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait BeginAndEndTimePropertyTrait
{
    /**
     * @var float
     */
    protected $beginTime = null;

    /**
     * @var float
     */
    protected $endTime = null;


    /**
     * Getter of $beginTime
     *
     * @return  float
     */
    public function getBeginTime()
    {
        return $this->beginTime;
    }


    /**
     * Getter of $endTime
     *
     * @return  float
     */
    public function getEndTime()
    {
        return $this->endTime;
    }


    /**
     * Setter of $beginTime
     *
     * @param   float   $beginTime
     * @return  static
     */
    public function setBeginTime($beginTime)
    {
        $this->beginTime = $beginTime;

        return $this;
    }


    /**
     * Setter of $endTime
     *
     * @param   float $endTime
     * @return  static
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }
}
