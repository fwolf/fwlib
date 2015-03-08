<?php
namespace FwlibTest\Util\Uuid;

use Fwlib\Util\Uuid\Base36;
use Fwlib\Util\Uuid\TimeBasedGeneratorTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class TimeBasedGeneratorTraitTest extends PHPUnitTestCase
{
    /**
     * Use algorithm like in test
     *
     * @see Base36
     * @return MockObject | TimeBasedGeneratorTrait
     */
    public function buildMock()
    {
        $mock = $this->getMockBuilder(TimeBasedGeneratorTrait::class)
            ->getMockForTrait();

        /** @noinspection PhpUndefinedFieldInspection */
        {
            $mock->base = 36;
            $mock->checkDigitMode = '3736';
            $mock->length = 25;
            $mock->lengthOfCustom = 7;
            $mock->lengthOfGroup = 2;
            $mock->lengthOfRandom = 6;
            $mock->randomMode = 'a0';
        }

        return $mock;
    }


    public function testAddCheckDigit()
    {
        $generator = $this->buildMock();

        $y = $generator->generate(null, null, true);
        $this->assertEquals($y, $generator->addCheckDigit($y));
    }


    public function testConvertBase()
    {
        $generator = $this->buildMock();

        $x = $this->reflectionCall($generator, 'convertBase', [35, 10, 36]);
        $this->assertEquals('z', $x);

        $x = $this->reflectionCall($generator, 'convertBase', [61, 10, 62]);
        $this->assertEquals('Z', $x);
    }


    public function testGenerateCustom()
    {
        $generator = $this->buildMock();

        $x = $this->reflectionCall($generator, 'generateCustom', ['']);
        $this->assertEquals($generator->lengthOfCustom, strlen($x));

        // Fill to demand length
        $x = $this->reflectionCall($generator, 'generateCustom', ['123']);
        $this->assertEquals($generator->lengthOfCustom, strlen($x));

        // Trim to demand length
        $x = $this->reflectionCall($generator, 'generateCustom', ['12345678']);
        $this->assertEquals('2345678', $x);
    }


    public function testGenerateGroup()
    {
        $generator = $this->buildMock();

        $x = $this->reflectionCall($generator, 'generateGroup', [0]);
        $this->assertEquals($generator->lengthOfGroup, strlen($x));

        // Fill to demand length
        $x = $this->reflectionCall($generator, 'generateGroup', [6]);
        $this->assertEquals('06', $x);

        // Trim to demand length
        $x = $this->reflectionCall($generator, 'generateGroup', ['123']);
        $this->assertEquals('23', $x);
    }


    public function testGenerateSecond()
    {
        $generator = $this->buildMock();

        $x = $this->reflectionCall($generator, 'generateSecond', [1]);
        $this->assertEquals('000001', $x);

        $x = $this->reflectionCall($generator, 'generateSecond', [2176782335]);
        $this->assertEquals('zzzzzz', $x);
    }


    public function testGenerateMicroSecond()
    {
        $generator = $this->buildMock();

        $y = 0.000002;
        $x = $this->reflectionCall($generator, 'generateMicroSecond', [$y]);
        $this->assertEquals('0002', $x);

        $y = 0.999999;
        $x = $this->reflectionCall($generator, 'generateMicroSecond', [$y]);
        $this->assertEquals('lflr', $x);
    }


    public function testParse()
    {
        $generator = $this->buildMock();

        // Group
        $ar = $generator->parse($generator->generate());
        $this->assertEquals('10', $ar['group']);
        $ar = $generator->parse($generator->generate('1'));
        $this->assertEquals('01', $ar['group']);

        // Custom
        $ar = $generator->parse($generator->generate('', '000'));
        $this->assertEquals('000', substr($ar['custom'], -3));

        // Parse data
        /** @noinspection SpellCheckingInspection */
        $ar = $generator->parse('mvqtti07x4a01a93alw6tz9qp');
        $this->assertEquals(1383575670, $ar['second']);
        $this->assertEquals(10264, $ar['microsecond']);
        $this->assertEquals('a0', $ar['group']);
        $this->assertEquals('1a93alw', $ar['custom']);
        $this->assertEquals('166.178.121.116', $ar['ip']);

        $this->assertNull($generator->parse(null));
    }


    public function testVerify()
    {
        $generator = $this->buildMock();

        $x = '';
        $this->assertFalse($generator->verify($x));

        /** @noinspection SpellCheckingInspection */
        {
            $x1 = 'mvqwzsaypm00sa2t8f0i9ooky';
            $x2 = 'mvqwzsaypm00sa2t8f0i9ook+';
            $x3 = 'mvqwzsaypm00sa2t8f0i9ookx';
        }

        $this->assertTrue($generator->verify($x1, true));

        $this->assertFalse($generator->verify($x2));

        $this->assertFalse($generator->verify($x3, true));

        $x = $generator->generate(null, null, true);
        $this->assertTrue($generator->verify($x, true));
    }
}
