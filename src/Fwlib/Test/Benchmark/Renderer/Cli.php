<?php
namespace Fwlib\Test\Benchmark\Renderer;

use Fwlib\Test\Benchmark\Marker;
use Fwlib\Test\Benchmark\RendererInterface;
use Fwlib\Test\Benchmark\RendererTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Renderer for command line interface
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Cli implements RendererInterface
{
    use RendererTrait;
    use UtilContainerAwareTrait;


    /**
     * Spaces before description
     *
     * @var string
     */
    protected $descriptionSpacer = '    ';

    /**
     * Width of duration column
     *
     * Format: N.M, N is integer part, and M is decimal part, the column can
     * be aligned by '.'.
     *
     * @var float
     */
    protected $durationWidth = 10.3;

    /**
     * Width of percent column
     *
     * @var int
     */
    protected $percentWidth = 6;

    /**
     * Separator row between header and marker rows
     *
     * @var string
     */
    protected $separatorRow =
        '---------------------------------------------------';


    /**
     * Format duration of a body line
     *
     * @param   Marker  $marker
     * @return  string
     */
    protected function formatDuration(Marker $marker)
    {
        $duration = $marker->getDuration();

        // Format duration before add background color
        $duration = sprintf("%{$this->durationWidth}f", $duration);

        // Space need not color
        $space = str_repeat(
            ' ',
            strlen($duration) - strlen(trim($duration))
        );
        $duration = trim($duration);

        // Add background color
        $color = $marker->getColor();
        if (!empty($color)) {
            $escapeColor = $this->getUtilContainer()->getEscapeColor();
            $duration = $escapeColor->paint($duration, '', '', $color);
        }

        $duration = $space . $duration;

        return $duration;
    }


    /**
     * Formation of single header or body line
     *
     * @param   string  $percent
     * @param   string  $duration       Width already adjusted
     * @param   string  $description
     * @return  string
     */
    protected function formatLine($percent, $duration, $description)
    {
        $output = sprintf("%{$this->percentWidth}s", $percent) .
            $duration .
            $this->descriptionSpacer .
            $description .
            PHP_EOL;

        return $output;
    }


    /**
     * Getter of output
     *
     * @return  string
     */
    public function getOutput()
    {
        $this->assignColor();

        $output = '';

        $escapeColor = $this->getUtilContainer()->getEscapeColor();
        foreach ($this->groups as $groupId => $group) {
            // Group description
            $output .= $escapeColor->paint($group->getDescription(), 'bold') .
                PHP_EOL;

            // Table header
            $duration = sprintf("%{$this->durationWidth}s", 'Dur');
            $output .= $this->formatLine('%', $duration, 'Marker Description');
            $output .= $this->separatorRow . PHP_EOL;

            // Marker rows
            if (0 < count($this->markers[$groupId])) {
                /** @var Marker $marker */
                foreach ($this->markers[$groupId] as $marker) {
                    $output .= $this->formatLine(
                        $marker->getPercent(),
                        $this->formatDuration($marker),
                        $marker->getDescription()
                    );
                }
            }

            // Auto stop has already set marker

            $output .= $this->separatorRow . PHP_EOL;

            // Total line
            $duration =
                sprintf("%{$this->durationWidth}f", $group->getDuration());
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
