<?php
namespace FwlibTest\Html\Ajax;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Html\Ajax\SelectBox;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SelectBoxTest extends PHPUnitTestCase
{
    /**
     * Tpl file to write, start from __DIR__ . '/SelectBox/'
     *
     * @var string
     */
    protected $outputFile = 'select-box.tpl';


    /**
     * @return SelectBox
     */
    public function buildMock()
    {
        $this->outputFile = __DIR__ . '/SelectBox/' . $this->outputFile;

        $selectBox = new SelectBox();

        return $selectBox;
    }


    public function testGet()
    {
        $selectBox = $this->buildMock();

        $html = $selectBox->get();
        //$this->selectBox->write($this->outputFile);
        $this->assertStringEqualsFile(
            $this->outputFile,
            $html
        );
    }
}
