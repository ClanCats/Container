<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    ContainerParser,
    Token as T
};

/**
 * This class should test the base parser functionality 
 */
class ContainerParserTest extends ParserTestCase
{
	public function testConstructTokenCleanup()
    {
    	// test removing of comments and spaces
    	$tokens = $this->tokensFromCode(':foo: "james" # bond');

    	$this->assertTokenTypesArray($tokens, [
    		T::TOKEN_PARAMETER,
    		T::TOKEN_ASSIGN,
    		T::TOKEN_SPACE,
    		T::TOKEN_STRING,
    		T::TOKEN_SPACE,
    		T::TOKEN_COMMENT
    	]);

        $parser = new ContainerParser($tokens);

        $this->assertTokenTypesArray($parser->getTokens(), [
    		T::TOKEN_PARAMETER,
    		T::TOKEN_ASSIGN,
    		T::TOKEN_STRING
    	]);
    }

    public function testSetTokens()
    {
		$parser = new ContainerParser([]);

    	$this->assertEquals(0, $parser->getTokenCount());

    	$tokens = [
    		'invalidKey' => new T(1, T::TOKEN_NUMBER, 42),
    		new T(1, T::TOKEN_NUMBER, 43),
    		42 => new T(1, T::TOKEN_NUMBER, 44),
    	];

    	$parser->setTokens($tokens);

    	// check the updated token count and reseted index
    	$this->assertEquals(3, $parser->getTokenCount());
    	$this->assertEquals(0, $parser->getIndex());

    	// check if the keys have been removed
    	$this->assertEquals([0, 1, 2], array_keys($parser->getTokens()));
    }
}