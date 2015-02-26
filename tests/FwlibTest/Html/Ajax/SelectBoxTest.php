<?php
namespace FwlibTest\Html\Ajax;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Html\Ajax\SelectBox;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SelectBoxTest extends PHPunitTestCase
{
    /**
     * Tpl file to write, start from __DIR__ . '/SelectBox/'
     *
     * @var string
     */
    protected $outputFile = 'select-box.tpl';

    protected $selectBox = null;


    public function __construct()
    {
        $this->outputFile = __DIR__ . '/SelectBox/' . $this->outputFile;

        $this->selectBox = new SelectBox();
    }


    public function testGet()
    {
        $html = $this->selectBox->get();
        //$this->selectBox->write($this->outputFile);
        $this->assertStringEqualsFile(
            $this->outputFile,
            $html
        );
    }
}
