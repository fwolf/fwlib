<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractRetriever implements RetrieverInterface
{
    use ConfigAwareTrait;
    use RequestAwareTrait;


    /**
     * {@inheritdoc}
     */
    abstract public function getListBody();


    /**
     * {@inheritdoc}
     */
    abstract public function getRowCount();
}
