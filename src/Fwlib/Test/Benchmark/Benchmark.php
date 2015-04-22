<?php
namespace Fwlib\Test\Benchmark;

use Fwlib\Test\Benchmark\Renderer\Cli;
use Fwlib\Test\Benchmark\Renderer\Web;
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
     * @var RendererInterface
     */
    protected $renderer = null;


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

        $renderer = $this->getRenderer();

        $result = $renderer->setGroups($this->groups)
            ->setMarkers($this->markers)
            ->getOutput();

        return $result;
    }


    /**
     * Getter of $renderer
     *
     * @return  RendererInterface
     */
    protected function getRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = $this->getUtilContainer()->getEnv()->isCli()
                ? new Cli()
                : new Web();
        }

        return $this->renderer;
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
     * Setter of $renderer
     *
     * @param   RendererInterface $renderer
     * @return  static
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
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
