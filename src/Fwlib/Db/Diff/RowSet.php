<?php
namespace Fwlib\Db\Diff;

use Fwlib\Db\Diff\Row;
use Fwlib\Util\AbstractUtilAware;

/**
 * Collection of db row change record
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RowSet extends AbstractUtilAware
{
    /**
     * Execute status code
     */
    const EXECUTE_STATUS_COMMITTED = 1;
    const EXECUTE_STATUS_NOT_EXECUTED = 0;
    const EXECUTE_STATUS_ROLLBACKED = -1;

    /**
     * Counter of changed rows
     *
     * @var int
     */
    protected $rowCount = 0;

    /**
     * @var array of Row
     */
    protected $rows = [];

    /**
     * Execute status
     *
     * @var int
     */
    protected $executeStatus = self::EXECUTE_STATUS_NOT_EXECUTED;


    /**
     * Constructor
     *
     * @param   string  $json
     */
    public function __construct($json = '')
    {
        if (!empty($json)) {
            $this->fromJson($json);
        }
    }


    /**
     * Add a changed row
     *
     * @param   Row     $row
     * @return  RowSet
     */
    public function addRow(Row $row)
    {
        $this->rows[] = $row;
        $this->rowCount ++;

        return $this;
    }


    /**
     * Load from json string
     *
     * @param   string  $json
     */
    protected function fromJson($json)
    {
        $info = $this->getUtil('Json')->decode($json, true);

        try {
            foreach ($info['rows'] as $row) {
                $this->addRow(new Row(
                    $row['table'],
                    $row['primaryKey'],
                    $row['old'],
                    $row['new']
                ));
            }
            $this->executeStatus = $info['executeStatus'];

        } catch (\Exception $e) {
            throw new \Exception(
                'Invalid json string to load: ' . $e->getMessage()
            );
        }
    }


    /**
     * Getter of $rowCount
     *
     * @return  int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }


    /**
     * Getter of $rows
     *
     * @return  array of Row
     */
    public function getRows()
    {
        return $this->rows;
    }


    /**
     * Is executed/committed ?
     *
     * @return  boolean
     */
    public function isCommitted()
    {
        return self::EXECUTE_STATUS_COMMITTED == $this->executeStatus;
    }


    /**
     * Is executed ?
     *
     * Return true when committed or rollbacked.
     *
     * @return  boolean
     */
    public function isExecuted()
    {
        return self::EXECUTE_STATUS_NOT_EXECUTED != $this->executeStatus;
    }


    /**
     * Is rollbacked ?
     *
     * @return  boolean
     */
    public function isRollbacked()
    {
        return self::EXECUTE_STATUS_ROLLBACKED == $this->executeStatus;
    }


    /**
     * Set to committed
     *
     * @return  RowSet
     */
    public function setCommitted()
    {
        $this->executeStatus = self::EXECUTE_STATUS_COMMITTED;

        return $this;
    }


    /**
     * Set to rollbacked
     *
     * @return  RowSet
     */
    public function setRollbacked()
    {
        $this->executeStatus = self::EXECUTE_STATUS_ROLLBACKED;

        return $this;
    }


    /**
     * Export to json string
     *
     * @return  string
     */
    public function toJson()
    {
        $json = $this->getUtil('Json');

        $rows = [];
        foreach ($this->rows as $row) {
            $rows[] = [
                'table'      => $row->getTable(),
                'primaryKey' => $row->getPrimaryKey(),
                'old'        => $row->getOld(),
                'new'        => $row->getNew(),
            ];
        }

        return $json->encodeUnicode(
            [
                'rowCount'      => $this->rowCount,
                'executeStatus' => $this->executeStatus,
                'rows'          => $rows,
            ]
        );
    }
}
