<?php
namespace Fwlib\Test\Benchmark;

use Fwlib\Test\Benchmark\Helper\BeginAndEndTimePropertyTrait;
use Fwlib\Test\Benchmark\Helper\DescriptionPropertyTrait;
use Fwlib\Test\Benchmark\Helper\DurationPropertyTrait;
use Fwlib\Test\Benchmark\Helper\IdPropertyTrait;

/**
 * Bench markers
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Marker
{
    use IdPropertyTrait;
    use DescriptionPropertyTrait;
    use BeginAndEndTimePropertyTrait;
    use DurationPropertyTrait;


    /**
     * Color to use when output
     *
     * @see RendererTrait::$colorMap
     *
     * @var string
     */
    protected $color = '';

    /**
     * @var int
     */
    protected $groupId = null;

    /**
     * Percent of total time of this group, 1 means 1%
     *
     * @var float
     */
    protected $percent = null;


    /**
     * @param   int     $groupId
     * @param   int     $markerId
     */
    public function __construct($groupId, $markerId)
    {
        $this->groupId = $groupId;
        $this->id = $markerId;
    }


    /**
     * Getter of $color
     *
     * @return  string
     */
    public function getColor()
    {
        return $this->color;
    }


    /**
     * Getter of $groupId
     *
     * @return  int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }


    /**
     * Getter of $percent
     *
     * @return  float
     */
    public function getPercent()
    {
        return $this->percent;
    }


    /**
     * Setter of $color
     *
     * @param   string $color
     * @return  static
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }


    /**
     * Setter of $percent
     *
     * @param   float $percent
     * @return  static
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }
}
