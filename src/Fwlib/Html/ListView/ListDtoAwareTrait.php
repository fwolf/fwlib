<?php
namespace Fwlib\Html\ListView;

/**
 * ListDtoAwareTrait
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ListDtoAwareTrait
{
    /**
     * @var ListDto
     */
    protected $listDto = null;


    /**
     * @return  ListDto
     */
    protected function getListDto()
    {
        return $this->listDto;
    }


    /**
     * @param   ListDto $listDto
     * @return  static
     */
    public function setListDto(ListDto $listDto)
    {
        $this->listDto = $listDto;

        return $this;
    }
}
