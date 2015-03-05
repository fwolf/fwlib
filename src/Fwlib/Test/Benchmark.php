<?php
namespace Fwlib\Test;

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
     * Group data
     *
     * {id: {desc, timeStart, timeEnd}}
     *
     * @var array
     */
    protected $group = [];

    /**
     * Current group id
     *
     * System will auto start group #0, another start will be group #1.
     *
     * @var int
     */
    protected $groupId = 0;

    /**
     * Mark data
     *
     * {groupId: {markId: {desc, time, dur, color, pct}}}
     *
     * @var array
     */
    protected $mark = [];

    /**
     * Mark id in group
     *
     * @var int
     */
    protected $markId = 0;


    /**
     * Display benchmark result
     *
     * @param   string  $options
     * @param   boolean $return     Return result instead echo
     */
    public function display($options = '', $return = false)
    {
        if ($this->getUtilContainer()->getEnv()->isCli()) {
            $result = $this->resultCli($options);
        } else {
            $result = $this->resultWeb($options);
        }

        if ($return) {
            return $result;
        } else {
            echo $result;
        }
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
        // Find max/min marker dur
        $dur_min = $this->mark[$groupId][0]['dur'];
        $dur_max = $dur_min;
        foreach ($this->mark[$groupId] as $markId => &$ar_mark) {
            if ($ar_mark['dur'] > $dur_max) {
                $dur_max = $ar_mark['dur'];
            } elseif ($ar_mark['dur'] < $dur_min) {
                $dur_min = $ar_mark['dur'];
            }
        }
        $dur = $dur_max - $dur_min;
        // Only 1 marker
        if (0 == $dur) {
            $dur = $dur_max;
        }

        // Amount of color
        $i_color = count($this->colorMap);
        if (1 > $i_color) {
            return;
        }

        // Split dur
        $step = $dur / $i_color;
        $ar_dur = [];
        // 6 color need 7 bound value
        for ($i = 0; $i < ($i_color + 1); $i ++) {
            $ar_dur[$i] = $step * $i;
        }

        // Compare, assign color
        foreach ($this->mark[$groupId] as $markId => &$mark) {
            // Compute dur percent
            $mark['pct'] = round(100 * $mark['dur'] / $this->group[$groupId]['dur']);

            // Skip user manual set color
            if (!empty($mark['color'])) {
                continue;
            }

            for ($i = 1; $i < ($i_color + 1); $i ++) {
                if (($mark['dur'] - $dur_min) <= $ar_dur[$i]) {
                    // 5.5 < 6, assign color[5]/color no.6
                    $mark['color'] = $this->colorMap[$i - 1];

                    // Quit for
                    $i = $i_color + 1;
                }
            }

            unset($mark);
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
     * Set a marker
     *
     * @param   string  $desc   Marker description
     * @param   string  $color  Specific color like '#FF0000' or 'red'
     * @return  float           Dur of this mark
     */
    public function mark($desc = '', $color = '')
    {
        if (0 == $this->markId) {
            $this->mark[$this->groupId] = [];
        }
        $ar = &$this->mark[$this->groupId][$this->markId];

        if (empty($desc)) {
            $desc = "Group #{$this->groupId}, Mark #{$this->markId}";
        }

        $ar['desc'] = $desc;
        $ar['time'] = $this->GetTime();
        if (0 == $this->markId) {
            $ar['dur'] = $ar['time'] - $this->group[$this->groupId]['timeStart'];
        } else {
            $ar['dur'] = $ar['time'] - $this->mark[$this->groupId][$this->markId - 1]['time'];
        }
        if (!empty($color)) {
            $ar['color'] = $color;
        }

        $this->markId ++;

        return $ar['dur'];
    }


    /**
     * Get result for cli output
     *
     * @param   string  $options
     * @return  string
     */
    public function resultCli($options = '')
    {
        $widthPct = 6;
        $widthDur = 10.3;
        $spaceDesc = '    ';
        $hr = str_repeat('-', 50);

        // Stop last group if it's not stopped
        if (!isset($this->group[$this->groupId]['timeEnd'])
            && isset($this->group[$this->groupId]['timeStart'])
        ) {
            $this->stop();
        }

        $output = '';

        $escapeColor = $this->getUtilContainer()->getEscapeColor();
        if (0 <= $this->groupId) {
            foreach ($this->group as $groupId => $ar_group) {
                $this->formatColor($groupId);

                $output .= $escapeColor->paint($ar_group['desc'], 'bold') .
                    PHP_EOL;

                $output .= sprintf('%' . $widthPct . 's', '%')
                    . sprintf('%' . $widthDur . 's', 'Dur Time')
                    . $spaceDesc
                    . 'Mark Description'
                    . PHP_EOL;
                $output .= $hr . PHP_EOL;

                // Markers
                if (0 < count($this->mark[$groupId])) {
                    foreach ($this->mark[$groupId] as $markId => $ar_mark) {
                        $time = $ar_mark['dur'];

                        // Format time before add bg color
                        $time = sprintf('%' . $widthDur . 'f', $time);

                        // Space need not color
                        $space = str_repeat(
                            ' ',
                            strlen($time) - strlen(trim($time))
                        );
                        $time = trim($time);

                        // Add bg color
                        if (!empty($ar_mark['color'])) {
                            $time = $escapeColor->paint(
                                $time,
                                '',
                                '',
                                $ar_mark['color']
                            );
                        }

                        $time = $space . $time;

                        $output .= sprintf('%' . $widthPct . 's', $ar_mark['pct'])
                            . $time
                            . $spaceDesc
                            . $ar_mark['desc']
                            . PHP_EOL;
                    }
                }

                // Stop has already set marker

                $output .= $hr . PHP_EOL;

                // Total
                $time = sprintf('%' . $widthDur . 'f', $ar_group['dur']);
                $output .= $escapeColor->paint('Total:', 'bold') . $time .
                    'ms' . PHP_EOL . PHP_EOL;

            }

            // Memory usage
            if (function_exists('memory_get_usage')) {
                $memory = number_format(memory_get_usage());
                $output .= $escapeColor->paint('Memory Usage: ', 'bold') .
                    $memory . ' bytes' . PHP_EOL;
            }
        }

        return $output;
    }


    /**
     * Get result for web output
     *
     * @param   string  $options
     * @return  string
     */
    public function resultWeb($options = '')
    {
        // Stop last group if it's not stopped
        if (!isset($this->group[$this->groupId]['timeEnd'])
            && isset($this->group[$this->groupId]['timeStart'])
        ) {
            $this->stop();
        }

        $html = '';

        if (0 <= $this->groupId) {
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
            foreach ($this->group as $groupId => $ar_group) {
                $this->formatColor($groupId);

                // Stop will create mark, so no 0=mark
                $html .= "  <table class='fwlib-benchmark__g{$groupId}'>\n";
                $html .= "    <caption>{$ar_group['desc']}</caption>\n";

                // Th
                $html .= <<<EOF

    <thead>
    <tr>
      <th>Dur Time</th>
      <th>Mark Description</th>
      <th>%</th>
    </tr>
    </thead>

EOF;
                // Markers
                if (0 < count($this->mark[$groupId])) {
                    $html .= "\n    <tbody>";
                    foreach ($this->mark[$groupId] as $markId => $ar_mark) {
                        $time = $this->formatTime($ar_mark['dur']);
                        // Bg color
                        if (!empty($ar_mark['color'])) {
                            $color = ' style="background-color: ' . $ar_mark['color'] . ';"';
                        } else {
                            $color = '';
                        }
                        $html .= <<<EOF

    <tr>
      <td{$color}>{$time}      </td>
      <td class='fwlib-benchmark__mark__desc'>{$ar_mark['desc']}</td>
      <td class='fwlib-benchmark__mark__pct'>{$ar_mark['pct']}%</td>
    </tr>

EOF;
                    }
                    $html .= "</tbody>\n";
                }

                // Stop has already set marker

                // Total
                $time = $this->formatTime($ar_group['dur']);
                $html .= <<<EOF

    <tr class="total">
      <td>{$time}</td>
      <td>Total</td>
      <td>-</td>
    </tr>

EOF;

                $html .= "\t</table>\n";
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
        }

        return $html;
    }


    /**
     * Start the timer
     *
     * @param   string  $desc   Group description
     */
    public function start($desc = '')
    {
        // Stop last group if it's not stopped
        if (!isset($this->group[$this->groupId]['timeEnd'])
            && isset($this->group[$this->groupId]['timeStart'])
        ) {
            $this->stop();
        }

        if (empty($desc)) {
            $desc = "Group #{$this->groupId}";
        }

        $this->group[$this->groupId]['timeStart'] = $this->GetTime();
        $this->group[$this->groupId]['desc'] = $desc;
    }


    /**
     * Stop current group
     */
    public function stop()
    {
        $this->mark('Stop');

        $time = $this->getTime();
        $ar = &$this->group[$this->groupId];
        $ar['timeEnd'] = $time;
        $ar['dur'] = $time - $ar['timeStart'];

        $this->groupId ++;
        $this->markId = 0;
    }
}
