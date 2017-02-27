<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\ContainerParser\{
    ContainerLexer,
    Token as T
};

class ContainerLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Assert the token types
     */
    protected function assertTokenTypes(string $code, array $expected)
    {
        $actualTokens = [];

        $lexer = new ContainerLexer($code);

        foreach($lexer->tokens() as $token)
        {
            $actualTokens[] = $token->getType();
        }

        $this->assertEquals($expected, $actualTokens);
    }

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

    public function testDoublicatedLinebreaks()
    {
        $this->assertTokenTypes("yes\n\n\nno", 
        [
            T::TOKEN_BOOL_TRUE,
            T::TOKEN_LINE,
            T::TOKEN_BOOL_FALSE,
        ]);
    }
}
