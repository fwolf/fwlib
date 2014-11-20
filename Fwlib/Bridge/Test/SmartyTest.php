<?php
namespace Fwlib\Bridge\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Bridge\Smarty;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class SmartyTest extends PHPunitTestCase
{
    private $smarty = null;


    public function __construct()
    {
        $this->smarty = new Smarty;
    }


    public function testAddConfigDirPrepend()
    {
        // For code coverage
        $this->smarty = new Smarty;


        $dir = $this->smarty->getConfigDir();
        $i = count($dir);

        $this->smarty->addConfigDirPrepend('foo/');

        $dir = $this->smarty->getConfigDir();
        $this->assertEquals($i + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);
    }


    public function testAddPluginsDirPrepend()
    {
        $dir = $this->smarty->getPluginsDir();
        $i = count($dir);

        $this->smarty->addPluginsDirPrepend('foo/');

        $dir = $this->smarty->getPluginsDir();
        $this->assertEquals($i + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);
    }


    public function testAddTemplateDirPrepend()
    {
        $dir = $this->smarty->getTemplateDir();
        $i = count($dir);

        $this->smarty->addTemplateDirPrepend('foo/');

        $dir = $this->smarty->getTemplateDir();
        $this->assertEquals($i + 1, count($dir));
        $this->assertEquals('foo/', $dir['']);
    }
}
