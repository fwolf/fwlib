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
     * Define color for markers in group, from fast to slow
     *
     * Notice: In cli mode, need use named color or escape color id, manual
     * set color also works.
     *
     * @var string[]
     */
    public $colorMap = [
        '#00FF00',
        '#CCFFCC',
        '#77FF77',
        '#FFCCCC',
        '#FF7777',
        '#FF0000'
    ];

    /**
     * @var Group[] {groupId: Group}
     */
    protected $groups = [];

    /**
     * @var array   {groupId: {markerId: Marker}}
     */
    protected $markers = [];


    /**
     * Assign cell background color
     *
     * Marker duration percent is also computed and set here.
     *
     * Arrange color by max/min marker duration and color map, and assign
     * color to each marker by its position between max and min duration.
     */
    protected function assignColor()
    {
        // Amount of color
        $colorCount = count($this->colorMap);
        if (1 > $colorCount) {
            return;
        }

        foreach ($this->groups as $groupId => $group) {
            if (empty($this->markers[$groupId])) {
                continue;
            }
            $markers = $this->markers[$groupId];

            $durationBounds = $this->getDurationBounds($markers);

            // Compare and assign color
            /** @var Marker $marker */
            foreach ($markers as $marker) {
                $markerDuration = $marker->getDuration();

                // Compute percent of marker duration vs group duration
                $percent =
                    round(100 * $markerDuration / $group->getDuration());
                $marker->setPercent($percent);

                // Skip if user had manual set color
                if (!empty($marker->getColor())) {
                    continue;
                }

                for ($i = 1; $i < ($colorCount + 1); $i ++) {
                    if ($markerDuration <= $durationBounds[$i]) {
                        // Eg: 5.5 < 6, will assign color[5](color no.6)
                        $marker->setColor($this->colorMap[$i - 1]);

                        // Quit for loop
                        break;
                    }
                }
            }
        }
    }


    /**
     * Get bound array of duration
     *
     * Note: Bound count is color count +1, eg: 6 color need 7 bound value
     *
     * @param   Marker[]    $markers
     * @return  float[]
     */
    protected function getDurationBounds(array $markers)
    {
        $colorCount = count($this->colorMap);

        // Find max/min marker duration
        $durations = [];
        /** @var Marker $marker */
        foreach ($markers as $marker) {
            $durations[] = $marker->getDuration();
        }

        $minDuration = min($durations);
        $maxDuration = max($durations);

        $durationDiff = $maxDuration - $minDuration;
        // Only 1 marker ?
        if (0 == $durationDiff) {
            $durationDiff = $maxDuration;
        }

        // Split duration by amount of color
        $step = $durationDiff / $colorCount;
        $durationBounds = [];
        // 6 color need 7 bound value
        for ($i = 0; $i < ($colorCount + 1); $i ++) {
            $durationBounds[$i] = $step * $i + $minDuration;
        }

        return $durationBounds;
    }


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
