<?php
namespace Fwlib\Test\Benchmark;

use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Benchmark tool for program execute time
 *
 * Time is measured by microsecond.
 *
 * Reference:
 * http://pear.php.net/package/Benchmark
 * http://www.phpclasses.org/browse/package/2244.html
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2009-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Benchmark
{
    use UtilContainerAwareTrait;


    /**
     * Define color for groups, from fast to slow
     *
     * @var array
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
     * Current group id
     *
     * System will auto start group #0, another start will be group #1.
     *
     * @var int
     */
    protected $groupId = 0;

    /**
     * Group data
     *
     * @var Group[] {groupId: Group}
     */
    protected $groups = [];

    /**
     * Marker id in groups
     *
     * @var int
     */
    protected $markerId = 0;

    /**
     * Marker data
     *
     * @var array   {groupId: {markerId: Marker}}
     */
    protected $markers = [];


    /**
     * Stop last group if its not stopped manually
     */
    protected function autoStop()
    {
        $group = $this->getCurrentGroup();

        if (!empty($group) && !empty($group->getBeginTime() &&
            empty($group->getEndTime()))
        ) {
            $this->stop();
        }
    }


    /**
     * Display benchmark result
     *
     * @return  string|void
     */
    public function display()
    {
        $output = $this->getOutput();

        echo $output;

        return null;
    }


    /**
     * Format cell bg color
     *
     * Split max/min marker dur by color number, and put each mark in it's
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
     * Format time to output
     *
     * @param   float   $time
     * @return  string
     */
    protected function formatTime($time)
    {
        // Split dur by '.' to make solid width
        $sec = floor($time);
        $usec = substr(strval(round($time - $sec, 3)), 2);
        $html = <<<EOF

        <span class='fwlib-benchmark__mark__sec'>{$sec}</span>
        <span class='fwlib-benchmark__mark__dot'>.</span>
        <span class='fwlib-benchmark__mark__usec'>{$usec}</span>

EOF;
        return $html;
    }


    /**
     * Get result for cli output
     *
     * @return  string
     */
    protected function getCliOutput()
    {
        $percentWidth = 6;
        $durationWidth = 10.3;
        $descriptionSpace = '    ';
        $hr = str_repeat('-', 50);

        $output = '';

        $escapeColor = $this->getUtilContainer()->getEscapeColor();
        foreach ($this->groups as $groupId => $group) {
            $this->formatColor($groupId);

            $output .= $escapeColor->paint($group->getDescription(), 'bold') .
                PHP_EOL;

            $output .= sprintf('%' . $percentWidth . 's', '%') .
                sprintf('%' . $durationWidth . 's', 'Dur Time') .
                $descriptionSpace .
                'Marker Description' .
                PHP_EOL;
            $output .= $hr . PHP_EOL;

            // Markers
            if (0 < count($this->markers[$groupId])) {
                /** @var Marker $marker */
                foreach ($this->markers[$groupId] as $markerId => $marker) {
                    $duration = $marker->getDuration();

                    // Format duration before add background color
                    $duration = sprintf('%' . $durationWidth . 'f', $duration);

                    // Space need not color
                    $space = str_repeat(
                        ' ',
                        strlen($duration) - strlen(trim($duration))
                    );
                    $duration = trim($duration);

                    // Add background color
                    $color = $marker->getColor();
                    if (!empty($color)) {
                        $duration = $escapeColor->paint(
                            $duration,
                            '',
                            '',
                            $color
                        );
                    }

                    $duration = $space . $duration;

                    $output .=
                        sprintf("%{$percentWidth}s", $marker->getPercent()) .
                        $duration .
                        $descriptionSpace .
                        $marker->getDescription() .
                        PHP_EOL;
                }
            }

            // Auto stop has already set marker

            $output .= $hr . PHP_EOL;

            // Total
            $duration = sprintf("%{$durationWidth}f", $group->getDuration());
            $output .= $escapeColor->paint('Total:', 'bold') . $duration .
                'ms' . PHP_EOL . PHP_EOL;

        }

        // Memory usage
        if (function_exists('memory_get_usage')) {
            $memory = number_format(memory_get_usage());
            $output .= $escapeColor->paint('Memory Usage: ', 'bold') .
                $memory . ' bytes' . PHP_EOL;
        }

        return $output;
    }


    /**
     * @return  Group|null
     */
    protected function getCurrentGroup()
    {
        $groupId = $this->groupId;

        return array_key_exists($groupId, $this->groups)
            ? $this->groups[$groupId]
            : null;
    }


    /**
     * Get benchmark result output
     *
     * @return  string
     */
    public function getOutput()
    {
        $this->autoStop();

        $result = $this->getUtilContainer()->getEnv()->isCli()
            ? $this->getCliOutput()
            : $this->getWebOutput();

        return $result;
    }


    /**
     * Get current time, measured by microsecond
     *
     * @return  float
     */
    protected function getTime()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec) * 1000;
    }


    /**
     * Get result for web output
     *
     * @return  string
     */
    protected function getWebOutput()
    {
        $html = '';
        $html .= <<<EOF

<style type="text/css" media="screen, print">
<!--
.fwlib-benchmark table, .fwlib-benchmark td {
  border: 1px solid #999;
  border-collapse: collapse;
  padding-left: 0.5em;
  padding-right: 0.5em;
}
.fwlib-benchmark table caption, .fwlib-benchmark__memory-usage {
  margin-top: 0.5em;
}
.fwlib-benchmark tr.total {
  background-color: #E5E5E5;
}

.fwlib-benchmark__mark__sec {
  display: inline-block;
  text-align: right;
  width: 4em;
}
.fwlib-benchmark__mark__dot {
  display: inline-block;
}
.fwlib-benchmark__mark__usec {
  display: inline-block;
  text-align: left;
  width: 3em;
}

.fwlib-benchmark__mark__desc {
}

.fwlib-benchmark__mark__pct {
  text-align: right;
}
-->
</style>

EOF;
        $html .= "<div class='fwlib-benchmark'>\n";
        /** @var Group $group */
        foreach ($this->groups as $groupId => $group) {
            $this->formatColor($groupId);

            // Auto stop will create marker, so no 0=mark
            $html .= "  <table class='fwlib-benchmark__g{$groupId}'>\n";
            $html .= "    <caption>{$group->getDescription()}</caption>\n";

            // Th
            $html .= <<<EOF

    <thead>
    <tr>
      <th>Dur Time</th>
      <th>Marker Description</th>
      <th>%</th>
    </tr>
    </thead>

EOF;
            // Markers
            if (0 < count($this->markers[$groupId])) {
                $html .= "\n    <tbody>";
                /** @var Marker $marker */
                foreach ($this->markers[$groupId] as $markerId => $marker) {
                    $duration = $this->formatTime($marker->getDuration());
                    // Bg color
                    $color = $marker->getColor();
                    if (!empty($color)) {
                        $color = ' style="background-color: ' . $color . ';"';
                    } else {
                        $color = '';
                    }
                    $html .= <<<EOF

    <tr>
      <td{$color}>{$duration}      </td>
      <td class='fwlib-benchmark__mark__desc'>{$marker->getDescription()}</td>
      <td class='fwlib-benchmark__mark__pct'>{$marker->getPercent()}%</td>
    </tr>

EOF;
                }
                $html .= "    </tbody>\n";
            }

            // Auto stop has already set marker

            // Total
            $duration = $this->formatTime($group->getDuration());
            $html .= <<<EOF

    <tr class="total">
      <td>{$duration}</td>
      <td>Total</td>
      <td>-</td>
    </tr>

EOF;

            $html .= "  </table>\n";
        }

        // Memory usage
        if (function_exists('memory_get_usage')) {
            $memory = number_format(memory_get_usage());
            $html .= <<<EOF

  <div class="fwlib-benchmark__memory-usage">
    Memory Usage: $memory
  </div>

EOF;
        }

        $html .= "</div>\n";

        return $html;
    }


    /**
     * Set a marker
     *
     * @param   string  $description    Marker description
     * @param   string  $color          Specific color like '#FF0000' or 'red'
     * @return  float                   Duration of this marker
     */
    public function mark($description = '', $color = '')
    {
        if (0 == $this->markerId) {
            $this->markers[$this->groupId] = [];
        }

        // Marker array of current group
        $markers = &$this->markers[$this->groupId];

        $marker = new Marker($this->groupId, $this->markerId);

        if (empty($description)) {
            $description = "Group #{$this->groupId}, Marker #{$this->markerId}";
        }
        $marker->setDescription($description);

        $beginTime = $this->getTime();
        $marker->setBeginTime($beginTime);

        if (0 == $this->markerId) {
            $group = $this->getCurrentGroup();
            $duration = $beginTime - $group->getBeginTime();
        } else {
            /** @var Marker $prevMarker */
            $prevMarker = $markers[$this->markerId - 1];
            $duration = $beginTime - $prevMarker->getBeginTime();
        }
        $marker->setDuration($duration);

        if (!empty($color)) {
            $marker->setColor($color);
        }

        $markers[$this->markerId] = $marker;

        $this->markerId ++;

        return $duration;
    }


    /**
     * Start the timer
     *
     * @param   string  $description    Group description
     */
    public function start($description = '')
    {
        $this->autoStop();

        if (empty($description)) {
            $description = "Group #{$this->groupId}";
        }

        $group = new Group($this->groupId);
        $group->setBeginTime($this->getTime());
        $group->setDescription($description);

        $this->groups[$this->groupId] = $group;
    }


    /**
     * Stop current group
     */
    public function stop()
    {
        $this->mark('Stop');

        $time = $this->getTime();
        $group = $this->getCurrentGroup();
        $group->setEndTime($time);
        $group->setDuration($time - $group->getBeginTime());

        $this->groupId ++;
        $this->markerId = 0;
    }
}
