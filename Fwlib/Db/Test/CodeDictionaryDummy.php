<?php
namespace Fwlib\Db\Test;

use Fwlib\Db\CodeDictionary;

class CodeDictionaryDummy extends CodeDictionary
{
    public function init()
    {
        parent::init();

        $this->setConfig('pk', 'code');

        $this->set(array(
            array(123,  'a'),
            array('bac',    2),
        ))->set(array(321,  'c'));

        return $this;
    }
}
