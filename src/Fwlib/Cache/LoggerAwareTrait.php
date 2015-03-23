<?php
namespace Fwlib\Cache;

/**
 * Logger Aware
 *
 * If {@see getLogger()} return null, means log feature is disabled, see
 * {@see log()}.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait LoggerAwareTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger = null;


    /**
     * Getter of logger instance
     *
     * This is public for access logs it records.
     *
     * @return  LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }


    /**
     * Record log, if logger instance is set
     *
     * @param   string $operate
     * @param   string $key
     * @param   bool   $success
     * @return  static
     */
    protected function log($operate, $key, $success)
    {
        $logger = $this->getLogger();

        if (!is_null($logger)) {
            $logger->log($operate, $key, $success);
        }

        return $this;
    }


    /**
     * Setter of logger instance
     *
     * @param   LoggerInterface $logger
     * @return  static
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
