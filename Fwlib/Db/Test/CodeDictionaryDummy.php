<?php
namespace Fwlib\Db\Test;

use Fwlib\Db\CodeDictionary;

class CodeDictionaryDummy extends CodeDictionary
{
    public function __construct()
    {
        $this->set(
            array(
                array(123,  'a'),
                array('bac', 2),
                array(321,  'c'),
            )
        );
    }
}
