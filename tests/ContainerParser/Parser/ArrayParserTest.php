<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ArrayParser,
    Nodes\ArrayNode,
    Nodes\ValueNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Token as T
};

class ArrayParserTest extends ParserTestCase
{
    protected function arrayParserFromCode(string $code) : ArrayParser 
    {
        return $this->parserFromCode(ArrayParser::class, $code);
    }

    protected function arrayNodeFromCode(string $code) : ArrayNode 
    {
        return $this->arrayParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ArrayParser::class, $this->arrayParserFromCode(''));
    }

    public function testArrayOfValues()
    {
        $array = $this->arrayNodeFromCode('"hello", "world"');

        $elements = $array->getElements();

        $this->assertCount(2, $elements);

        foreach(['hello' , 'world'] as $k => $value)
        {
            $this->assertEquals($k, $elements[$k]->getKey());
            $this->assertInstanceOf(ValueNode::class, $elements[$k]->getValue());
            $this->assertEquals($value, $elements[$k]->getValue()->getRawValue());
        }
    }

    public function testAssocArrayIdentifierKey()
    {
        $array = $this->arrayNodeFromCode('name: "James", code: "007"');
        $elements = $array->getElements();

        $this->assertCount(2, $elements);

        foreach([['name', 'James'], ['code', '007']] as $k => list($key, $value))
        {
            $this->assertEquals($key, $elements[$k]->getKey());
            $this->assertInstanceOf(ValueNode::class, $elements[$k]->getValue());
            $this->assertEquals($value, $elements[$k]->getValue()->getRawValue());
        }
    }

    public function testAssocArrayStringKey()
    {
        $array = $this->arrayNodeFromCode('"name": "James", "code": "007"');
        $elements = $array->getElements();

        $this->assertCount(2, $elements);

        foreach([['name', 'James'], ['code', '007']] as $k => list($key, $value))
        {
            $this->assertEquals($key, $elements[$k]->getKey());
            $this->assertInstanceOf(ValueNode::class, $elements[$k]->getValue());
            $this->assertEquals($value, $elements[$k]->getValue()->getRawValue());
        }
    }

    public function testAssocArrayNumberKey()
    {
        $array = $this->arrayNodeFromCode('7: "James", 8: "007"');
        $elements = $array->getElements();

        $this->assertCount(2, $elements);

        foreach([[7, 'James'], [8, '007']] as $k => list($key, $value))
        {
            $this->assertEquals($key, $elements[$k]->getKey());
            $this->assertInstanceOf(ValueNode::class, $elements[$k]->getValue());
            $this->assertEquals($value, $elements[$k]->getValue()->getRawValue());
        }
    }

    public function testArrayMixed()
    {
        $array = $this->arrayNodeFromCode('42, "James", "Bond", some: "property", 5: 10, "B"');
        $elements = $array->getElements();

        $this->assertCount(6, $elements);

        $expected = [
            [0, 42],
            [1, "James"],
            [2, "Bond"],
            ['some', 'property'],
            [5, 10],
            [3, "B"]
        ];

        foreach($expected as $k => list($key, $value))
        {
            $this->assertEquals($key, $elements[$k]->getKey());
            $this->assertInstanceOf(ValueNode::class, $elements[$k]->getValue());
            $this->assertEquals($value, $elements[$k]->getValue()->getRawValue());
        }
    }

    public function testArrayNested()
    {
        $array = $this->arrayNodeFromCode('title: "Cookies", ingredients: {"milk", "chocolate"}');
        $elements = $array->getElements();

        $this->assertEquals('Cookies', $elements[0]->getValue()->getRawValue());
        $this->assertEquals('ingredients', $elements[1]->getKey());

        $subarray = $elements[1]->getValue();
        $this->assertInstanceOf(ArrayNode::class, $subarray);  

        $ingredients = $subarray->getElements();
        $this->assertCount(2, $ingredients);
    }

    public function testArraySuperNested()
    {
        $array = $this->arrayNodeFromCode('title: "Cookies", ingredients: {{name: "Milk", amount: "1L"}, {name: "chocolate", amount: "12gramm"}}');
        $elements = $array->getElements();
        $elements = $elements[1]->getValue()->getElements();

        foreach ([['name', 'Milk', 'amount', '1L'], ['name', 'chocolate', 'amount', '12gramm']] as $k => list($key1, $value1, $key2, $value2)) 
        {
            $ingredient = $elements[$k]->getValue()->getElements();

            $this->assertEquals($key1, $ingredient[0]->getKey());
            $this->assertEquals($key2, $ingredient[1]->getKey());

            $this->assertEquals($value1, $ingredient[0]->getValue()->getRawValue());
            $this->assertEquals($value2, $ingredient[1]->getValue()->getRawValue());
        }
    }
}