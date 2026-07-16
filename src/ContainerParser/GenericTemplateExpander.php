<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2026 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser;

use ClanCats\Container\Exceptions\ContainerInterpreterException;

use ClanCats\Container\ContainerParser\Nodes\{
    ArgumentArrayNode,
    ArrayNode,
    AssignableNode,
    GenericDefinitionNode,
    GenericMaterializationNode,
    MetaDataAssignmentNode,
    ParameterReferenceNode,
    ServiceDefinitionNode,
    ServiceMethodCallNode,
    ServiceReferenceNode,
    TypeParameterPlaceholderNode,
    ValueNode
};

/**
 * Expands a generic template into a concrete service definition.
 *
 * The generic body has already been parsed once into a `ServiceDefinitionNode`
 * template carrying `TypeParameterPlaceholderNode`s. Expanding a materialization
 * means deep cloning that template and replacing every placeholder with a
 * concrete node built from the supplied type argument tokens — a pure node to
 * node substitution with no re-lexing or re-parsing.
 */
class GenericTemplateExpander
{
    /**
     * Expand the given generic with the type arguments of the materialization.
     *
     * @param GenericDefinitionNode         $generic
     * @param GenericMaterializationNode    $materialization
     * @return ServiceDefinitionNode
     */
    public function expand(GenericDefinitionNode $generic, GenericMaterializationNode $materialization) : ServiceDefinitionNode
    {
        $template = $generic->getTemplate();

        if (is_null($template)) {
            throw new ContainerInterpreterException("The generic \"{$generic->getName()}\" has no parsed template to expand.");
        }

        $parameters = $generic->getTypeParameters();
        $arguments = $materialization->getTypeArguments();

        /** @var array<string, Token> $substitutions */
        $substitutions = $parameters === [] ? [] : array_combine($parameters, $arguments);

        // deep clone so the stored template is never mutated and repeated
        // materializations stay fully independent of each other.
        $definition = clone $template;
        $definition->setName($materialization->getName());
        $definition->setIsOverride($materialization->isOverride());

        $this->substituteDefinition($definition, $substitutions, $materialization->getGenericName());

        return $definition;
    }

    /**
     * Substitute all placeholders inside a service definition in place.
     *
     * @param array<string, Token>      $substitutions
     */
    protected function substituteDefinition(ServiceDefinitionNode $definition, array $substitutions, string $genericName) : void
    {
        // the class name may itself be a type parameter (e.g. `@x<T>: T`)
        $classNamePlaceholder = $definition->getClassNamePlaceholder();
        if ($classNamePlaceholder !== null) {
            $token = $this->argumentToken($classNamePlaceholder->getName(), $substitutions, $genericName);

            if (!$token->isType(Token::TOKEN_IDENTIFIER)) {
                throw new ContainerInterpreterException("The type argument for \"{$classNamePlaceholder->getName()}\" cannot be used as the service class name of generic \"{$genericName}\", a class name is required.");
            }

            $definition->setClassName($token->getValue());
            $definition->setClassNamePlaceholder(null);
        }

        if ($definition->hasArguments()) {
            $this->substituteArguments($definition->getArguments(), $substitutions, $genericName);
        }

        foreach ($definition->getConstructionActions() as $action) {
            if ($action instanceof ServiceMethodCallNode && $action->hasArguments()) {
                $this->substituteArguments($action->getArguments(), $substitutions, $genericName);
            }
        }

        foreach ($definition->getMetaDataAssignemnts() as $meta) {
            if ($meta instanceof MetaDataAssignmentNode && $meta->hasData()) {
                $data = $meta->getData();
                if ($data !== null) {
                    $this->substituteArray($data, $substitutions, $genericName);
                }
            }
        }
    }

    /**
     * Substitute placeholders inside an argument vector in place.
     *
     * @param array<string, Token>      $substitutions
     */
    protected function substituteArguments(ArgumentArrayNode $arguments, array $substitutions, string $genericName) : void
    {
        $resolved = [];

        foreach ($arguments->getArguments() as $argument) {
            $resolved[] = $this->substituteAssignable($argument, $substitutions, $genericName);
        }

        $arguments->setArguments($resolved);
    }

    /**
     * Substitute placeholders inside an array node in place.
     *
     * @param array<string, Token>      $substitutions
     */
    protected function substituteArray(ArrayNode $array, array $substitutions, string $genericName) : void
    {
        foreach ($array->getElements() as $element) {
            $element->setValue($this->substituteAssignable($element->getValue(), $substitutions, $genericName));
        }
    }

    /**
     * Resolve a single assignable node, replacing it if it is a placeholder and
     * recursing into nested arrays.
     *
     * @param array<string, Token>      $substitutions
     */
    protected function substituteAssignable(AssignableNode $node, array $substitutions, string $genericName) : AssignableNode
    {
        if ($node instanceof TypeParameterPlaceholderNode) {
            return $this->resolvePlaceholder($node, $substitutions, $genericName);
        }

        if ($node instanceof ArrayNode) {
            $this->substituteArray($node, $substitutions, $genericName);
        }

        return $node;
    }

    /**
     * Resolve a placeholder used in an argument / array value position.
     *
     * @param array<string, Token>      $substitutions
     */
    protected function resolvePlaceholder(TypeParameterPlaceholderNode $placeholder, array $substitutions, string $genericName) : AssignableNode
    {
        $token = $this->argumentToken($placeholder->getName(), $substitutions, $genericName);

        // a `T::class` placeholder that receives a plain class name keeps the
        // `::class` wrapping and resolves to the leading backslash class name
        // string, everything else is substituted verbatim.
        if ($placeholder->isClassConstant() && $token->isType(Token::TOKEN_IDENTIFIER)) {
            return new ValueNode('\\' . ltrim($token->getValue(), '\\'), ValueNode::TYPE_CLASS_NAME);
        }

        return $this->tokenToNode($token, $genericName);
    }

    /**
     * Fetch the type argument token for the given type parameter name.
     *
     * @param array<string, Token>      $substitutions
     */
    protected function argumentToken(string $name, array $substitutions, string $genericName) : Token
    {
        if (!isset($substitutions[$name])) {
            throw new ContainerInterpreterException("No type argument was supplied for the type parameter \"{$name}\" of generic \"{$genericName}\".");
        }

        return $substitutions[$name];
    }

    /**
     * Convert a type argument token into the matching assignable node, mirroring
     * how the argument parsers turn tokens into nodes.
     */
    protected function tokenToNode(Token $token, string $genericName) : AssignableNode
    {
        if ($token->isType(Token::TOKEN_DEPENDENCY)) {
            return new ServiceReferenceNode(substr($token->getValue(), 1));
        }

        if ($token->isType(Token::TOKEN_PARAMETER)) {
            return new ParameterReferenceNode(substr($token->getValue(), 1));
        }

        if ($token->isValue()) {
            return ValueNode::fromToken($token);
        }

        throw new ContainerInterpreterException("The type argument \"{$token->getValue()}\" of generic \"{$genericName}\" cannot be used in this position; a bare class name is only valid as the service class name or wrapped in `::class`.");
    }
}
