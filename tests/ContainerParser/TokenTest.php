<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\ContainerParser\{
    Token as T
};

class TokenTest extends \PHPUnit\Framework\TestCase
{
    public function testLine()
    {
        $token = new T(42, T::TOKEN_IDENTIFIER, null);
        $this->assertEquals(42, $token->getLine());
    }

    public function testType()
    {
        $string = new T(42, T::TOKEN_STRING, null);
        $this->assertEquals(T::TOKEN_STRING, $string->getType());

        $string = new T(42, T::TOKEN_NUMBER, null);
        $this->assertEquals(T::TOKEN_NUMBER, $string->getType());
    }

    public function testValue()
    {
        // bool 
        $boolTrue = new T(42, T::TOKEN_BOOL_TRUE, false);
        $this->assertTrue($boolTrue->getValue());

        $boolFalse = new T(42, T::TOKEN_BOOL_FALSE, null);
        $this->assertFalse($boolFalse->getValue());

        // string
        // single quotes
        $string = new T(42, T::TOKEN_STRING, "'a'");
        $this->assertEquals('a', $string->getValue());

        // double quotes
        $string = new T(42, T::TOKEN_STRING, '"b"');
        $this->assertEquals('b', $string->getValue());

        // escaped slashes
        $string = new T(42, T::TOKEN_STRING, '"b\\""');
        $this->assertEquals('b"', $string->getValue());

        // number
        $number = new T(42, T::TOKEN_NUMBER, 1);
        $this->assertEquals(1, $number->getValue());

        $number = new T(42, T::TOKEN_NUMBER, "42");
        $this->assertEquals(42, $number->getValue());

        $number = new T(42, T::TOKEN_NUMBER, "42.42");
        $this->assertEquals(42.42, $number->getValue());

        $number = new T(42, T::TOKEN_NUMBER, "-1000");
        $this->assertEquals(-1000, $number->getValue());

        // null
        $null = new T(42, T::TOKEN_NULL, true);
        $this->assertNull($null->getValue());
    }

    public function testIsValue()
    {
        foreach ([
            T::TOKEN_STRING,
            T::TOKEN_NUMBER,
            T::TOKEN_NULL,
            T::TOKEN_BOOL_TRUE,
            T::TOKEN_BOOL_FALSE
        ] as $type) 
        {
            $token = new T(42, $type, null);

            $this->assertTrue($token->isValue());
        }
    }
}
