<?php
namespace Fwlib\Base;

use Fwlib\Bridge\Smarty;
use Fwlib\Cache\CachedCaller;
use Fwlib\Html\ListTable;
use Fwlib\Html\ListView\ListView;
use Fwlib\Net\Curl;
use Fwlib\Validator\Validator;

/**
 * Service Container
 *
 * This is a working service container, can be used directly in production, or
 * extend to add more service classes or creation methods.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ServiceContainer implements ServiceContainerInterface
{
    use ServiceContainerTrait;


    /**
     * {@inheritdoc}
     */
    protected function buildClassMap()
    {
        $classMap = [
            'CachedCaller' => CachedCaller::class,
            'Curl'         => Curl::class,
            'ListView'     => ListView::class,
            'Smarty'       => Smarty::class,
            'Validator'    => Validator::class,
        ];

        return $classMap;
    }


    /**
     * Create ListTable service instance
     *
     * @return  ListTable
     */
    protected function createListTable()
    {
        return new ListTable($this->getSmarty());
    }


    /**
     * @return  CachedCaller
     */
    public function getCachedCaller()
    {
        return $this->get('CachedCaller');
    }


    /**
     * @return  Curl
     */
    public function getCurl()
    {
        return $this->get('Curl');
    }


    /**
     * @return  ListTable
     * @deprecated  Use ListView
     */
    public function getListTable()
    {
        return $this->get('ListTable');
    }


    /**
     * @return  ListView
     */
    public function getListView()
    {
        return $this->get('ListView');
    }


    /**
     * @return  Smarty
     */
    public function getSmarty()
    {
        return $this->get('Smarty');
    }


    /**
     * @return  Validator
     */
    public function getValidator()
    {
        return $this->get('Validator');
    }
}
