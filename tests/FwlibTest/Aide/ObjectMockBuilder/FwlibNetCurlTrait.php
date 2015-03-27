<?php
namespace FwlibTest\Aide\ObjectMockBuilder;

use Fwlib\Net\Curl;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FwlibNetCurlTrait
{
    /** @var string|array */
    protected $curlPostParams;

    /** @var string */
    protected $curlPostResult;

    /** @var string */
    protected $curlPostUrl;


    /**
     * @return  MockObject | Curl
     */
    protected function buildCurlMock()
    {
        /** @var PHPUnitTestCase|static $this */
        $mock = $this->getMock(Curl::class, ['post']);

        $mock->expects($this->any())
            ->method('post')
            ->will($this->returnCallback(function($url, $params) {
                $this->curlPostUrl = $url;
                $this->curlPostParams = $params;

                return $this->curlPostResult;
            }));

        /** @var Curl $mock */
        $mock->setSslVerify(false);

        return $mock;
    }
}
