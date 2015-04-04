<?php
namespace Fwlib\Html\ListView;

use Fwlib\Html\ListView\Exception\InvalidBodyException;

/**
 * ListDto
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListDto
{
    /**
     * List body
     *
     * 2 dim array, 2nd dimension must use assoc index.
     *
     * @var array[]
     */
    protected $body = null;

    /**
     * List head
     *
     * Have only 1 dimension, should be assoc indexed.
     *
     * If head is integer indexed, the 2nd dimension of {@see $body} should be
     * integer indexed too and have same order with head. Better not do this,
     * use associate index for easier understanding.
     *
     * @var array
     */
    protected $head = [];

    /**
     * Row count of whole list, not just this page
     *
     * Value {@see ListView::ROW_COUNT_NOT_SET} means not set yet, 0 means
     * list contains no data.
     *
     * Row count can be changed by:
     *  - {@see setRowCount()}
     *  - {@see ListView::setRowCount()}
     *  - {@see ListView::setBody()} with $updateRowCount parameter
     *
     * @var int
     */
    protected $rowCount = ListView::ROW_COUNT_NOT_SET;


    /**
     * @return  \array[]
     */
    public function getBody()
    {
        return $this->body;
    }


    /**
     * @return  array
     */
    public function getHead()
    {
        return $this->head;
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
     * @param   \array[] $body
     * @return  static
     * @throws  InvalidBodyException
     */
    public function setBody($body)
    {
        if (!empty($body) && !is_array(current($body))) {
            throw new InvalidBodyException(
                'List body need to be 2 dimension array'
            );
        }

        $this->body = $body;

        return $this;
    }


    /**
     * @param   array $head
     * @return  static
     */
    public function setHead(array $head)
    {
        $this->head = $head;

        return $this;
    }


    /**
     * Setter of $rowCount
     *
     * @param   int $rowCount
     * @return  static
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;

        return $this;
    }
}
