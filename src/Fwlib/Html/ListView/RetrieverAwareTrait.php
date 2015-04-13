<?php
namespace Fwlib\Html\ListView;

/**
 * RetrieverAwareTrait
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RetrieverAwareTrait
{
    /**
     * @var RetrieverInterface
     */
    protected $retriever = null;


    /**
     * @return  RetrieverInterface
     */
    protected function getRetriever()
    {
        return $this->retriever;
    }


    /**
     * @param   RetrieverInterface $retriever
     * @return  static
     */
    public function setRetriever(RetrieverInterface $retriever)
    {
        $this->retriever = $retriever;

        return $this;
    }
}
