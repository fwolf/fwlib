<?php
namespace Fwlib\Test\Benchmark\Renderer;

use Fwlib\Test\Benchmark\Marker;
use Fwlib\Test\Benchmark\RendererInterface;
use Fwlib\Test\Benchmark\RendererTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Renderer for web interface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Web implements RendererInterface
{
    use RendererTrait;
    use UtilContainerAwareTrait;


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
     * Getter of output
     *
     * @return  string
     */
    public function getOutput()
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
}
