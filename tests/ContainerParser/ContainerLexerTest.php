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

        foreach($this->tokensFromCode($code) as $token)
        {
            $actualTokens[] = $token->getType();
        }

        $this->assertEquals($expected, $actualTokens);
    }

    /**
     * Helper to get tokens from a code string
     */
    protected function tokensFromCode(string $code) : array
    {
        $lexer = new ContainerLexer($code);
        return $lexer->tokens();
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
        $this->assertTokenTypes("true\n\n\nfalse", 
        [
            T::TOKEN_BOOL_TRUE,
            T::TOKEN_LINE,
            T::TOKEN_BOOL_FALSE,
        ]);
    }

    public function testScalarString()
    {
        $this->assertTokenTypes("'hello'\"world\"", [T::TOKEN_STRING, T::TOKEN_STRING]);

        // escaping
        $string = $this->tokensFromCode('"James \' Bond"')[0];
        $this->assertEquals("James ' Bond", $string->getValue());

        // breaks
        $string = $this->tokensFromCode("'James \n Bond'")[0];
        $this->assertEquals("James \n Bond", $string->getValue());

        // utf8mb4
        $string = $this->tokensFromCode("'🐌🐌🐌'")[0];
        $this->assertEquals("🐌🐌🐌", $string->getValue());

        // types inside
        $this->assertTokenTypes("'1'", [T::TOKEN_STRING]);
        $this->assertTokenTypes("''", [T::TOKEN_STRING]);
        $this->assertTokenTypes("'true'", [T::TOKEN_STRING]);
        $this->assertTokenTypes("'\"\"'", [T::TOKEN_STRING]);
    }

    public function testScalarNumber()
    {
        $this->assertTokenTypes("-1", [T::TOKEN_NUMBER]);

        $this->assertTokenTypes("1", [T::TOKEN_NUMBER]);

        $this->assertTokenTypes("0", [T::TOKEN_NUMBER]);

        $this->assertTokenTypes("0.1", [T::TOKEN_NUMBER]);
        $this->assertTokenTypes("0.1 'a' 3.14", [
            T::TOKEN_NUMBER, 
            T::TOKEN_SPACE,
            T::TOKEN_STRING,
            T::TOKEN_SPACE, 
            T::TOKEN_NUMBER,
        ]);
    }

    public function testScalarBool()
    {
        $this->assertTokenTypes("true", [T::TOKEN_BOOL_TRUE]);

        $this->assertTokenTypes("false", [T::TOKEN_BOOL_FALSE]);

        $this->assertTokenTypes("false,true", [
            T::TOKEN_BOOL_FALSE, 
            T::TOKEN_SEPERATOR,
            T::TOKEN_BOOL_TRUE,
        ]);
    }

    public function testScalarNull()
    {
        $this->assertTokenTypes("null", [T::TOKEN_NULL]);
    }

    public function testDependency()
    {
        $this->assertTokenTypes("@foo", [T::TOKEN_DEPENDENCY]);
        $this->assertTokenTypes("@foo_bar", [T::TOKEN_DEPENDENCY]);
        $this->assertTokenTypes("@foo.bar", [T::TOKEN_DEPENDENCY]);
        $this->assertTokenTypes("@foo/bar", [T::TOKEN_DEPENDENCY]);
        $this->assertTokenTypes("@foo-bar", [T::TOKEN_DEPENDENCY]);

        // simple assing
        $this->assertTokenTypes("@router: Acme\\Router", [
            T::TOKEN_DEPENDENCY,
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE,
            T::TOKEN_IDENTIFIER
        ]);
    }

    public function testProtoypeDefinition()
    {
        $this->assertTokenTypes("@router?: Acme\\Router", [
            T::TOKEN_DEPENDENCY,
            T::TOKEN_PROTOTYPE,
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE,
            T::TOKEN_IDENTIFIER
        ]);
    }

    public function testParameter()
    {
        $this->assertTokenTypes(":foo", [T::TOKEN_PARAMETER]);
        $this->assertTokenTypes(":foo_bar", [T::TOKEN_PARAMETER]);
        $this->assertTokenTypes(":foo.bar", [T::TOKEN_PARAMETER]);
        $this->assertTokenTypes(":foo/bar", [T::TOKEN_PARAMETER]);
        $this->assertTokenTypes(":foo-bar", [T::TOKEN_PARAMETER]);

        $this->assertTokenTypes(":password: '123456'", [
            T::TOKEN_PARAMETER,
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE,
            T::TOKEN_STRING
        ]);

        $this->assertTokenTypes(":needed: true, false", [
            T::TOKEN_PARAMETER,
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE,
            T::TOKEN_BOOL_TRUE, 
            T::TOKEN_SEPERATOR, 
            T::TOKEN_SPACE, 
            T::TOKEN_BOOL_FALSE, 
        ]);
    }

    public function testComments()
    {
        $this->assertTokenTypes("// :foo", [T::TOKEN_COMMENT]);
        $this->assertTokenTypes("# :foo", [T::TOKEN_COMMENT]);
        $this->assertTokenTypes("/* true */", [T::TOKEN_COMMENT]);
        $this->assertTokenTypes("/* foo \n\n\n bar */", [T::TOKEN_COMMENT]);
    }

    public function testKeywords()
    {
        $this->assertTokenTypes("use Acme\Test", [T::TOKEN_USE, T::TOKEN_IDENTIFIER]);
        $this->assertTokenTypes("import foo/bar", [T::TOKEN_IMPORT, T::TOKEN_IDENTIFIER]);
    }

    public function testBraces()
    {
        $this->assertTokenTypes("()", [T::TOKEN_BRACE_OPEN, T::TOKEN_BRACE_CLOSE]);

        $this->assertTokenTypes("Ship(@engine, :name)", [
            T::TOKEN_IDENTIFIER, 
            T::TOKEN_BRACE_OPEN, 
            T::TOKEN_DEPENDENCY,
            T::TOKEN_SEPERATOR, 
            T::TOKEN_SPACE, 
            T::TOKEN_PARAMETER, 
            T::TOKEN_BRACE_CLOSE, 
        ]);
    }

    public function testCalls()
    {
        $this->assertTokenTypes("- doThis: @damn", [
            T::TOKEN_MINUS, 
            T::TOKEN_SPACE,
            T::TOKEN_IDENTIFIER, 
            T::TOKEN_ASSIGN, 
            T::TOKEN_SPACE, 
            T::TOKEN_DEPENDENCY, 
        ]);
    }
}
