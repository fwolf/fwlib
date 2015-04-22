<?php
namespace Fwlib\Test\Benchmark\Renderer;

use Fwlib\Test\Benchmark\Marker;
use Fwlib\Test\Benchmark\RendererInterface;
use Fwlib\Test\Benchmark\RendererTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Renderer for command line interface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Cli implements RendererInterface
{
    use RendererTrait;
    use UtilContainerAwareTrait;


    /**
     * Getter of output
     *
     * @return  string
     */
    public function getOutput()
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
}
