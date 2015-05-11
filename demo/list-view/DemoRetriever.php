<?php
namespace FwlibDemo\ListView;

use Fwlib\Bridge\Adodb;
use Fwlib\Html\ListView\AbstractRetriever;

/**
 * DemoRetriever
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DemoRetriever extends AbstractRetriever
{
    /**
     * @var Adodb
     */
    protected $dbConnection = null;

    /**
     * @var string
     */
    protected $table = '';


    /**
     * {@inheritdoc}
     */
    public function getListBody()
    {
        $configs = array_merge(
            $this->getSqlConfigs(),
            ['SELECT' => ['uuid', 'title', 'age', 'credit', 'joindate']]
        );

        return $this->dbConnection->execute($configs)
            ->getArray();
    }


    /**
     * {@inheritdoc}
     */
    public function getRowCount()
    {
        $configs = array_merge(
            $this->getSqlConfigs(),
            ['SELECT' => 'COUNT(1) as c']
        );

        unset($configs['LIMIT']);
        unset($configs['ORDERBY']);

        $result = $this->dbConnection->execute($configs);
        return $result->fields['c'];
    }


    /**
     * @return  array
     */
    protected function getSqlConfigs()
    {
        $configs = [
            'FROM'      => $this->table,
            'WHERE'     => [
                'age > 30',
            ],
        ];

        $limit = $this->getSqlLimit();
        if (!empty($limit)) {
            $configs['LIMIT'] = $limit;
        }

        $orderBy = $this->getSqlOrderby();
        if (!empty($orderBy)) {
            $configs['ORDERBY'] = $orderBy;
        }

        return $configs;
    }


    /**
     * @return  string
     */
    protected function getSqlLimit()
    {
        $pageSize = $this->getConfig('pageSize');
        $page = $this->getRequest()->getPage();

        return !empty($pageSize)
            ? ($page - 1) * $pageSize . ", $pageSize"
            : '';
    }


    /**
     * @return  array
     */
    protected function getSqlOrderby()
    {
        $orderBy = $this->getRequest()->getOrderBy();

        $sqlOrderBy = [];
        foreach ($orderBy as $key => $dir) {
            $sqlOrderBy[] = "$key $dir";
        }

        return $sqlOrderBy;
    }


    /**
     * @param   Adodb   $adodb
     * @return  static
     */
    public function setDb(Adodb $adodb)
    {
        $this->dbConnection = $adodb;

        return $this;
    }


    /**
     * @param   string  $table
     * @return  string
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}
