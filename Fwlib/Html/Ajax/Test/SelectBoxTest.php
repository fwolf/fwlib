<?php
namespace Fwlib\Html\Ajax\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Html\Ajax\SelectBox;

/**
 * Test for Fwlib\Html\Ajax\SelectBox
 *
 * @package     Fwlib\Html\Ajax\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-21
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
