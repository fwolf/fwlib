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
     * @var Group[]
     */
    protected $groups = [];

    /**
     * @var Marker[]
     */
    protected $markers = [];


    /**
     * Format cell bg color
     *
     * Split max/min marker dur by color number, and put each mark in its
     * color.
     *
     * @param   int $groupId
     */
    protected function formatColor($groupId)
    {
        $markers = $this->markers[$groupId];

        // Find max/min marker duration
        $durations = [];
        foreach ($markers as $marker) {
            /** @var Marker $marker */
            $durations[] = $marker->getDuration();
        }

        $minDuration = min($durations);
        $maxDuration = max($durations);

        $durationPeriod = $maxDuration - $minDuration;
        // Only 1 marker ?
        if (0 == $durationPeriod) {
            $durationPeriod = $maxDuration;
        }

        // Amount of color
        $colorCount = count($this->colorMap);
        if (1 > $colorCount) {
            return;
        }

        // Split duration by amount of color
        $step = $durationPeriod / $colorCount;
        $durationBounds = [];
        // 6 color need 7 bound value
        for ($i = 0; $i < ($colorCount + 1); $i ++) {
            $durationBounds[$i] = $step * $i;
        }

        // Compare and assign color
        $group = $this->groups[$groupId];
        foreach ($markers as $markId => $marker) {
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
                if (($markerDuration - $minDuration) <= $durationBounds[$i]) {
                    // Eg: 5.5 < 6, will assign color[5](color no.6)
                    $marker->setColor($this->colorMap[$i - 1]);

                    // Quit for loop
                    $i = $colorCount + 1;
                }
            }
        }
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
