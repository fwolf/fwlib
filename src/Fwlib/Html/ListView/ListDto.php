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
    protected $data = [];

    /**
     * List title
     *
     * Have only 1 dimension, integer or assoc indexed.
     *
     * @var array
     */
    protected $title = [];


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
     * @param   \array[] $data
     * @return  static
     * @throws  InvalidDataException
     */
    public function setData(array $data)
    {
        if (!is_array(current($data))) {
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
}
