<?php
namespace ClanCats\Container\Tests\TestCases;

use ClanCats\Container\ContainerParser\{
	ContainerLexer
};

abstract class LexerTestCase extends \PHPUnit\Framework\TestCase
{
	/**
     * Assert an array of tokens with the expected types
     * 
     * @param array 			$code
     * @param array 			$expected
     */
    protected function assertTokenTypesArray(array $tokens, array $expected)
    {
        $actualTypes = [];

        foreach($tokens as $token)
        {
            $actualTypes[] = $token->getType();
        }

        $this->assertEquals($expected, $actualTypes);
    }

	/**
     * Assert the token types
     * 
     * @param string 			$code
     * @param array 			$expected
     */
    protected function assertTokenTypes(string $code, array $expected)
    {
        $this->assertTokenTypesArray($this->tokensFromCode($code), $expected);
    }

    /**
     * Helper to get tokens from a code string
     * 
     * @param string 		$code
     * @return array
     */
    protected function tokensFromCode(string $code) : array
    {
        $lexer = new ContainerLexer($code);

        return $lexer->tokens();
    }
}