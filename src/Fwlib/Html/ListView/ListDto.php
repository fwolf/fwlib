<?php
namespace Fwlib\Html\ListView;

use Fwlib\Html\ListView\Exception\InvalidDataException;

/**
 * ListDto
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListDto
{
    /**
     * List data
     *
     * 2 dim array, 2nd dimension must use assoc index.
     *
     * @var array[]
     */
    protected $data = null;

    /**
     * List title
     *
     * Have only 1 dimension, should be assoc indexed.
     *
     * If title is integer indexed, the 2nd dimension of data should be
     * integer indexed too and have same order with title, better not do this.
     *
     * @var array
     */
    protected $title = [];

    /**
     * Total rows of whole list, not just this page
     *
     * Value -1 means not set yet, 0 means list contains no data.
     *
     * @var int
     */
    protected $totalRows = -1;


    /**
     * @return  \array[]
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @return  array
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Getter of $totalRows
     *
     * @return  int
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }


    /**
     * @param   \array[] $data
     * @return  static
     * @throws  InvalidDataException
     */
    public function setData($data)
    {
        if (!empty($data) && !is_array(current($data))) {
            throw new InvalidDataException(
                'Data need to be 2 dimension array'
            );
        }

        $this->data = $data;

        return $this;
    }


    /**
     * @param   array $title
     * @return  static
     */
    public function setTitle(array $title)
    {
        $this->title = $title;

        return $this;
    }


    /**
     * Setter of $totalRows
     *
     * @param   int $totalRows
     * @return  static
     */
    public function setTotalRows($totalRows)
    {
        $this->totalRows = $totalRows;

        return $this;
    }
}
