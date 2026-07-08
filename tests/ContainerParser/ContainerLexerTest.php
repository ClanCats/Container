<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\Tests\TestCases\LexerTestCase;

use ClanCats\Container\ContainerParser\{
    ContainerLexer,
    Token as T
};

class ContainerLexerTest extends LexerTestCase
{
    /**
     * Computes the 1-indexed source line on which the given marker text starts.
     * Used so expected line numbers are derived programmatically instead of by
     * hand-counting, which is error prone for anything but the shortest snippets.
     */
    private function expectedLineOf(string $code, string $marker) : int
    {
        $pos = strpos($code, $marker);
        $this->assertNotFalse($pos, "marker \"$marker\" not found in test source");
        return substr_count(substr($code, 0, $pos), "\n") + 1;
    }

    /**
     * @param array<T> $tokens
     */
    private function findParameterToken(array $tokens, string $name) : ?T
    {
        foreach ($tokens as $token) {
            if ($token->getType() === T::TOKEN_PARAMETER && $token->getValue() === $name) {
                return $token;
            }
        }
        return null;
    }

    /**
     * @param array<T> $tokens
     */
    private function findFirstOfType(array $tokens, int $type) : ?T
    {
        foreach ($tokens as $token) {
            if ($token->getType() === $type) {
                return $token;
            }
        }
        return null;
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

    public function testMultilineStrings()
    {
        $this->assertTokenTypes(<<<'CODE'
        :foo: "myfunc() then
            return 'bla bla'
        end"
        CODE, [T::TOKEN_PARAMETER, T::TOKEN_ASSIGN, T::TOKEN_SPACE, T::TOKEN_STRING]);
    }

    public function testHeredocStrings()
    {
        // regular multiline strings collapse repeated whitespace / indentation
        $string = $this->tokensFromCode(<<<'CODE'
        "line one
            line two
        line three"
        CODE)[0];
        $this->assertEquals("line one\n line two\nline three", $string->getValue());

        // a heredoc block opts out of that collapsing
        $string = $this->tokensFromCode(<<<'CODE'
        <<<EOT
        line one
            line two
        line three
        EOT
        CODE)[0];
        $this->assertEquals("line one\n    line two\nline three", $string->getValue());

        // a heredoc is still just a single string token
        $this->assertTokenTypes(<<<'CODE'
        <<<EOT
        hello
        EOT
        CODE, [T::TOKEN_STRING]);

        // single quotes inside a heredoc don't terminate it early
        $string = $this->tokensFromCode(<<<'CODE'
        <<<EOT
        it's "fine".
        EOT
        CODE)[0];
        $this->assertEquals("it's \"fine\".", $string->getValue());

        // a line that merely starts with the tag does not close the block,
        // only an exact match on its own line does
        $string = $this->tokensFromCode(<<<'CODE'
        <<<EOT
        EOTAG
        line two
        EOT
        CODE)[0];
        $this->assertEquals("EOTAG\nline two", $string->getValue());

        // whitespace outside of a heredoc block is still collapsed as usual
        $tokens = $this->tokensFromCode(<<<'CODE'
        a    <<<EOT
        raw   text
        EOT
           b
        CODE);
        $this->assertTokenTypesArray($tokens, [
            T::TOKEN_IDENTIFIER,
            T::TOKEN_SPACE,
            T::TOKEN_STRING,
            T::TOKEN_LINE,
            T::TOKEN_SPACE,
            T::TOKEN_IDENTIFIER,
        ]);
        $this->assertEquals('raw   text', $tokens[2]->getValue());
    }

    public function testHeredocPreservesLineNumbers()
    {
        // a heredoc spans multiple source lines; tokens after it must keep their
        // real line numbers so lexer/parser error messages point at the right line.
        //
        //   line 1: :before: 'a'
        //   line 2: :doc: <<<EOT
        //   line 3: hello
        //   line 4: world
        //   line 5: EOT
        //   line 6: :after: 'b'
        $tokens = $this->tokensFromCode(":before: 'a'\n:doc: <<<EOT\nhello\nworld\nEOT\n:after: 'b'");

        $findParameter = function (string $name) use ($tokens) {
            foreach ($tokens as $token) {
                if ($token->getType() === T::TOKEN_PARAMETER && $token->getValue() === $name) {
                    return $token;
                }
            }
            return null;
        };

        $this->assertNotNull($findParameter(':before'));
        $this->assertNotNull($findParameter(':after'));

        // the parameter before the heredoc is unaffected
        $this->assertEquals(1, $findParameter(':before')->getLine());

        // the parameter after the heredoc must still report its real source line (6),
        // i.e. the heredoc extraction must not "eat" the lines it spanned
        $this->assertEquals(6, $findParameter(':after')->getLine());
    }

    public function testHeredocLineNumbersAcrossMultipleHeredocs()
    {
        // two heredocs (reusing the same tag name) in a single file; every token
        // after each one must keep accumulating the real line count rather than
        // resetting or double-counting across the two placeholder substitutions.
        $code = ":before: 'a'\n:x: <<<EOT\none\nEOT\n:mid: 'm'\n:y: <<<EOT\nAAA\nBBB\nEOT\n:after: 'z'";

        $tokens = $this->tokensFromCode($code);

        $before = $this->findParameterToken($tokens, ':before');
        $mid = $this->findParameterToken($tokens, ':mid');
        $after = $this->findParameterToken($tokens, ':after');

        $this->assertNotNull($before);
        $this->assertNotNull($mid);
        $this->assertNotNull($after);

        $this->assertEquals($this->expectedLineOf($code, ':before'), $before->getLine());
        $this->assertEquals($this->expectedLineOf($code, ':mid'), $mid->getLine());
        $this->assertEquals($this->expectedLineOf($code, ':after'), $after->getLine());
    }

    public function testHeredocLineNumbersWithCrlf()
    {
        // Windows-style line endings throughout a heredoc block; every "\r\n"
        // must still count as exactly one line for line-number tracking.
        $code = ":before: 'a'\r\n:doc: <<<EOT\r\nhello\r\nworld\r\nEOT\r\n:after: 'b'";

        $tokens = $this->tokensFromCode($code);

        $before = $this->findParameterToken($tokens, ':before');
        $after = $this->findParameterToken($tokens, ':after');

        $this->assertNotNull($before);
        $this->assertNotNull($after);

        $this->assertEquals($this->expectedLineOf($code, ':before'), $before->getLine());
        $this->assertEquals($this->expectedLineOf($code, ':after'), $after->getLine());
    }

    public function testHeredocWithEmptyBody()
    {
        // a heredoc whose entire body is a single blank line - the minimal /
        // degenerate case for both the extraction regex and the "lost newlines"
        // line-number bookkeeping.
        $code = ":doc: <<<EOT\n\nEOT\n:after: 'b'";

        $tokens = $this->tokensFromCode($code);
        $string = $this->findFirstOfType($tokens, T::TOKEN_STRING);
        $after = $this->findParameterToken($tokens, ':after');

        $this->assertNotNull($string);
        $this->assertEquals('', $string->getValue());

        $this->assertNotNull($after);
        $this->assertEquals($this->expectedLineOf($code, ':after'), $after->getLine());
    }

    public function testHeredocRequiresClosingTagOnOwnLine()
    {
        // the closing tag must be preceded by its own newline (i.e. sit alone on
        // its own line); a heredoc with no blank body line before an adjacent
        // closing tag is therefore not recognised as a heredoc at all, and the
        // literal "<" falls through to the normal tokenizer and fails predictably
        // rather than silently producing a corrupted token stream.
        //
        // this is positioned starting on line 2 (not line 1) to avoid a separate,
        // pre-existing, unrelated off-by-one in ContainerLexerException's line
        // reporting for errors on the very first source line.
        $this->expectException(\ClanCats\Container\Exceptions\ContainerLexerException::class);
        $this->tokensFromCode(":before: 'a'\n<<<EOT\nEOT");
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
            T::TOKEN_OPTIONAL,
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

    public function testClassNames()
    {
        $this->assertTokenTypes(":foo: \Foo\Bar::class", [
            T::TOKEN_PARAMETER, 
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE, 
            T::TOKEN_CLASS_NAME, 
        ]);

        $this->assertTokenTypes(":foo: Foo\Bar::class", [
            T::TOKEN_PARAMETER, 
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE, 
            T::TOKEN_CLASS_NAME, 
        ]);

        $this->assertTokenTypes(":foo: Bar::class", [
            T::TOKEN_PARAMETER, 
            T::TOKEN_ASSIGN,
            T::TOKEN_SPACE, 
            T::TOKEN_CLASS_NAME, 
        ]);
    }
}
