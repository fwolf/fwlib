<?php
namespace Fwlib\Mvc\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Mvc\UrlGenerator;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlGeneratorTest extends PHPunitTestCase
{
    /**
     * @return  UrlGenerator
     */
    protected function buildMock()
    {
        $_SERVER['HTTP_HOST'] = 'domain.tld';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['REQUEST_URI'] = '/index.php?foo=bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_GET = array(
            'foo' => 'bar',
        );

        $urlGenerator = $this->getMock(
            'Fwlib\Mvc\UrlGenerator',
            null
        );

        return $urlGenerator;
    }


    public function testFullUrl()
    {
        $urlGenerator = $this->buildMock();

        $this->assertEquals(
            'https://domain.tld/index.php?foo=bar',
            $urlGenerator->getFullUrl()
        );

        // Without tailing '/', original path '/index.php' will kept
        $urlGenerator->setBaseUrl('http://net.com');
        $this->assertEquals(
            'http://net.com/index.php?foo=bar',
            $urlGenerator->getFullUrl()
        );

        // With tailing '/', the new path is '/'
        $urlGenerator->setBaseUrl('http://net.com/');
        $this->assertEquals(
            'http://net.com/?foo=bar',
            $urlGenerator->getFullUrl()
        );

        $url = 'http://username:password@hostname/path?arg=value#anchor';
        $urlGenerator->setFullUrl($url);
        $this->assertEquals($url, $urlGenerator->getFullUrl());
    }


    public function testSimpleUrl()
    {
        $urlGenerator = $this->buildMock();

        $this->assertEquals(
            '?foo=bar',
            $urlGenerator->getUrl()
        );

        $urlGenerator->setParameter('f2', 42);
        $this->assertEquals(
            '?foo=bar&f2=42',
            $urlGenerator->getUrl()
        );

        $urlGenerator->setParameters(array(
            'f3' => 420,
            'f4' => '4200',
        ));
        $this->assertEquals(
            '?foo=bar&f2=42&f3=420&f4=4200',
            $urlGenerator->getUrl()
        );

        $urlGenerator->unsetParameter('f4');
        $this->assertEquals(
            '?foo=bar&f2=42&f3=420',
            $urlGenerator->getUrl()
        );

        $urlGenerator->unsetParameters(array('f2', 'f3'));
        $this->assertEquals(
            '?foo=bar',
            $urlGenerator->getUrl()
        );

        $urlGenerator->unsetAllParameters();
        $this->assertEmpty($urlGenerator->getUrl());
    }
}
