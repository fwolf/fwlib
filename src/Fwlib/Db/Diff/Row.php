<?php
namespace Fwlib\Db\Diff;


/**
 * Record change of a single db row
 *
 * Some table/row meta info is also included to help execute.
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Row
{
    /**
     * Operate mode: INSERT|DELETE|UPDATE
     *
     * @var string
     */
    protected $mode = '';

    /**
     * Row data after change, null for DELETE mode
     *
     * Primary key value is included here.
     *
     * @var array|null  {column: value}
     */
    protected $new = [];

    /**
     * Row data before change, null for INSERT mode
     *
     * Primary key value is included here.
     *
     * @var array|null  {column: value}
     */
    protected $old = [];

    /**
     * Table primary keys
     *
     * @var array|string
     */
    protected $primaryKey = '';

    /**
     * Affected table
     *
     * @var string
     */
    protected $table = '';


    /**
     * Constructor
     *
     * @param   string          $table
     * @param   array|string    $primaryKey
     * @param   array|null      $old
     * @param   array|null      $new
     */
    public function __construct($table, $primaryKey, $old, $new)
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->mode = $this->determineMode($old, $new);
        $this->old = $old;
        $this->new = $new;

        $this->removeDuplicateColumn();
    }


    /**
     * Determine which mode this row should be
     *
     * @param   array|null  $old
     * @param   array|null  $new
     * @return  string
     */
    protected function determineMode($old, $new)
    {
        if (empty($old) && !empty($new)) {
            return 'INSERT';

        } elseif (!empty($old) && empty($new)) {
            return 'DELETE';

        } elseif (!empty($old) && !empty($new)) {
            return 'UPDATE';

        } else {
            throw new \Exception(
                "Diff mode determine failed, both old and new data array are empty"
            );
        }
    }


    /**
     * Getter of $mode
     *
     * @return  string
     */
    public function getMode()
    {
        return $this->mode;
    }


    /**
     * Getter of $new, or single key in it
     *
     * @param   string  $key
     * @return  array
     */
    public function getNew($key = '')
    {
        if (empty($key)) {
            return $this->new;

        } else {
            return $this->new[$key];
        }
    }


    /**
     * Get $new excluding primary key column
     *
     * @return  array
     */
    public function getNewWithoutPrimaryKey()
    {
        return array_diff_key(
            $this->new,
            array_fill_keys((array)$this->primaryKey, null)
        );
    }


    /**
     * Getter of $old, or single key in it
     *
     * @param   string  $key
     * @return  array
     */
    public function getOld($key = '')
    {
        if (empty($key)) {
            return $this->old;

        } else {
            return $this->old[$key];
        }
    }


    /**
     * Get $old excluding primary key column
     *
     * @return  array
     */
    public function getOldWithoutPrimaryKey()
    {
        return array_diff_key(
            $this->old,
            array_fill_keys((array)$this->primaryKey, null)
        );
    }


    /**
     * Getter of $primaryKey
     *
     * @return  array|string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }


    /**
     * Getter of $table
     *
     * @return  array
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * In UPDATE mode, remove duplicate column in $old and $new
     */
    protected function removeDuplicateColumn()
    {
        if ('UPDATE' != $this->mode) {
            return;
        }

        $duplicate = array_intersect_assoc($this->old, $this->new);

        // Primary key column will not be removed even they didn't change
        foreach ((array)$this->primaryKey as $key) {
            unset($duplicate[$key]);
        }

        $this->old = array_diff_key($this->old, $duplicate);
        $this->new = array_diff_key($this->new, $duplicate);
    }
}
