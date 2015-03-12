<?php
namespace FwlibTest;

use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * Initializer of all PHP system function being mocked
 *
 * Put in root of test case namespace with name start with 'Aaa' to make sure
 * PHPUnit run it first.
 *
 * The trait need use in every test case want to mock system function. I tried
 * make a container to store them for reuse, not work.
 *
 * The PHPUnit will scan and execute constructor of every test case before run
 * them(include this one), so code in constructor may use system function first
 * and make system function mock not work.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AaaMockInitializerTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * Register PHP native function mock here (with define)
     *
     * If all test case constructor are empty, this can be deleted.
     */
    public function testMockRegister()
    {
        $factory = $this->getFunctionMockFactory();

        $factory->get('Fwlib\Util', 'extension_loaded');
        $factory->get('Fwlib\Util', 'filter_input_array');
        $factory->get('Fwlib\Util', 'session_status');
        $factory->get('Fwlib\Util', 'session_start');
        $factory->get('Fwlib\Util', 'session_destroy');

        $this->assertTrue(true);
    }


    public function testMockSuccessful()
    {
        $factory = $this->getFunctionMockFactory();
        $extensionLoadedMock =
            $factory->get('FwlibTest', 'extension_loaded', true);

        $extensionLoadedMock->setResult(false);
        $this->assertFalse(extension_loaded('pcre'));

        $extensionLoadedMock->setResult(true);
        $this->assertTrue(extension_loaded('pcre'));

        $extensionLoadedMock->disable();
    }
}
