<?php
namespace Fwlib\Cache;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Logger implements LoggerInterface
{
    /**
     * Actual log data
     *
     * @var array
     */
    protected $logs = [];


    /**
     * {@inheritdoc}
     */
    public function getLogs()
    {
        return $this->logs;
    }


    /**
     * {@inheritdoc}
     */
    public function log($operate, $key, $success)
    {
        $this->logs[] = [
            'operate' => $operate,
            'key'     => $key,
            'success' => $success,
        ];

        return $this;
    }
}
