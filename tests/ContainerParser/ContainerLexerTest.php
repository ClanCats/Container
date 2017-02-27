<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\ContainerParser\{
    ContainerLexer
};

class ContainerLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $lexer = new ContainerLexer('test');
        $this->assertEquals('test', $lexer->code());

        // doublicated
        $lexer = new ContainerLexer('test  bar      foo');
        $this->assertEquals('test bar foo', $lexer->code());

        // trim
        $lexer = new ContainerLexer(' test ');
        $this->assertEquals('test', $lexer->code());
    }
}
