<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2022 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser;

use ClanCats\Container\Exceptions\ContainerInterpreterException;

use ClanCats\Container\ContainerNamespace;
use ClanCats\Container\ServiceDefinition;
use ClanCats\Container\ServiceArguments;

use ClanCats\Container\ContainerParser\{
    ContainerLexer,
    Parser\ScopeParser
};

use ClanCats\Container\ContainerParser\Nodes\{
    BaseNode as Node,

    // the nodes
    ValueNode,
    ArrayNode,
    ScopeNode,
    ScopeImportNode,
    ParameterDefinitionNode,
    ServiceDefinitionNode,
    ParameterReferenceNode,
    ServiceReferenceNode,
    ServiceMethodCallNode,
    MetaDataAssignmentNode,
    ArgumentArrayNode
};

class ContainerInterpreter
{
    /**
     * The container definitions namesapce 
     * 
     * @var ContainerNamespace
     */
    protected $namespace;

    /**
     * Construct a new container file interpreter
     * 
     * @param ContainerNamespace                $namespace
     * @return void
     */
    public function __construct(ContainerNamespace $namespace)
    {
        $this->namespace = $namespace;
    }

	/**
	 * Handle a container file scope
	 * 
	 * @param ScopeNode 			$scope
	 * @return void
	 */
    public function handleScope(ScopeNode $scope) 
    {
    	foreach($scope->getNodes() as $node)
    	{
    		if ($node instanceof ScopeImportNode)
    		{
    			$this->handleScopeImport($node);
    		}
            elseif ($node instanceof ParameterDefinitionNode)
            {
                $this->handleParameterDefinition($node);
            }
            elseif ($node instanceof ServiceDefinitionNode)
            {
                $this->handleServiceDefinition($node);
            }
    		else 
    		{
    			throw new ContainerInterpreterException("Unexpected node in scope found.");
    		}
    	}
    }

    /**
     * Handle a scope import statement
     * 
     * @param ScopeImportNode           $import
     * @return void
     */
    public function handleScopeImport(ScopeImportNode $import)
    {
        $path = $import->getPath();

        if (empty($path))
        {
            throw new ContainerInterpreterException("An import statement cannot be empty.");
        }

        $code = $this->namespace->getCode($path);

        // after retrieving new code we have 
        // to start a new lexer & and parser 
        $lexer = new ContainerLexer($code);
        $parser = new ScopeParser($lexer->tokens());
        $scopeNode = $parser->parse();

        unset($lexer, $parser);

        if (!$scopeNode instanceof ScopeNode) {
            throw new ContainerInterpreterException("Could not retrieve a valid scope from import");
        }

        // and continue handling the importet scope
        $this->handleScope($scopeNode);
    }  

    /**
     * Handle a parameter definition
     * 
     * @param ParameterDefinitionNode 			$definition
     * @return void
     */
    public function handleParameterDefinition(ParameterDefinitionNode $definition) 
    {
        if ($this->namespace->hasParameter($definition->getName()) && $definition->isOverride() === false)
        {
            throw new ContainerInterpreterException("A parameter named \"{$definition->getName()}\" is already defined, you can prefix the definition with \"override\" to get around this error.");
        }

        $node = $definition->getValue();

        if ($node instanceof ValueNode) {
            $this->namespace->setParameter($definition->getName(), $node->getRawValue());
        } 
        elseif ($node instanceof ArrayNode) {
            $this->namespace->setParameter($definition->getName(), $node->convertToNativeArray());
        }
        else {
            throw new ContainerInterpreterException("Invalid parameter value given for key \"{$definition->getName()}\".");
        }
    }

    /**
     * Create final service arguments from an arguments array node
     * 
     * @param ArgumentArrayNode         $argumentsNode
     * @return ServiceArguments
     */
    protected function createServiceArgumentsFromNode(ArgumentArrayNode $argumentsNode) : ServiceArguments
    {
        $arguments = $argumentsNode 
            ->getArguments();

        $definition = new ServiceArguments();

        foreach($arguments as $argument)
        {
            if ($argument instanceof ServiceReferenceNode)
            {
                $definition->addDependency($argument->getName());
            }
            elseif ($argument instanceof ParameterReferenceNode)
            {
                $definition->addParameter($argument->getName());
            }
            elseif ($argument instanceof ArrayNode)
            {
                $definition->addRaw($argument->convertToNativeArray());
            }
            elseif ($argument instanceof ValueNode)
            {
                $definition->addRaw($argument->getRawValue());
            }
            else 
            {
                throw new ContainerInterpreterException("Unable to handle argument node of type \"" . get_class($argument) . "\".");
            }
        }

        return $definition;
    }

    /**
     * Handle a service definition
     *
     * @param ServiceDefinitionNode $definition
     *
     * @return void
     */
    public function handleServiceDefinition(ServiceDefinitionNode $definition) 
    {
        if ($this->namespace->hasService($definition->getName()) && $definition->isOverride() === false && $definition->isUpdate() === false)
        {
            throw new ContainerInterpreterException("A service / alias named \"{$definition->getName()}\" is already defined, you can prefix the definition with \"override\" to get around this error.");
        }

        // special case if an alias is beeing defined
        if ($definition->isAlias()) {
            $this->namespace->setAlias($definition->getName(), $definition->getAliasTarget()->getName());
            return;
        }

        // create a new service or fetch an existing 
        // one in case of an update
        if ($definition->isUpdate()) 
        {
            if (!$this->namespace->hasService($definition->getName())) {
                throw new ContainerInterpreterException("A service named \"{$definition->getName()}\" is beeing updated, but the service has not been defined yet.");
            }

            // fetch the existing service
            $service = $this->namespace->getServices()[$definition->getName()];

        } else  {
            // create a service definition from the node
            $service = new ServiceDefinition($definition->getClassName());
        }

        if ($definition->hasArguments() && $service instanceof ServiceDefinition) 
        {
            $arguments = $definition
                ->getArguments() // get the definitions arguments
                ->getArguments(); // and the argument array from the object

            foreach($arguments as $argument)
            {
                if ($argument instanceof ServiceReferenceNode)
                {
                    $service->addDependencyArgument($argument->getName());
                }
                elseif ($argument instanceof ParameterReferenceNode)
                {
                    $service->addParameterArgument($argument->getName());
                }
                elseif ($argument instanceof ArrayNode)
                {
                    $service->addRawArgument($argument->convertToNativeArray());
                }
                elseif ($argument instanceof ValueNode)
                {
                    $service->addRawArgument($argument->getRawValue());
                }
                else 
                {
                    throw new ContainerInterpreterException("Unable to handle argument node of type \"" . get_class($argument) . "\".");
                }
            }
        }

        // handle construction actions
        foreach($definition->getConstructionActions() as $action)
        {
            if ($action instanceof ServiceMethodCallNode && $service instanceof ServiceDefinition)
            {
                if ($action->hasArguments()) 
                {
                    $service->addMethodCall($action->getName(), $this->createServiceArgumentsFromNode($action->getArguments()));
                } else {
                    $service->calls($action->getName());
                }
            }
            else 
            {
                throw new ContainerInterpreterException("Invalid construction action of type \"" . get_class($action) . "\" given.");
            }
        }

        // handle meta data
        foreach($definition->getMetaDataAssignemnts() as $meta)
        {
            if ($meta instanceof MetaDataAssignmentNode && $service instanceof ServiceDefinition)
            {
                if ($meta->hasData()) 
                {
                    $service->addMetaData($meta->getKey(), $meta->getData()->convertToNativeArray());
                } else {
                    $service->addMetaData($meta->getKey(), []);
                }
            }
            else 
            {
                throw new ContainerInterpreterException("Invalid meta data assignment \"" . get_class($meta) . "\" given.");
            }
        }

        // add the node to the namespace
        $this->namespace->setService($definition->getName(), $service);
    }
}

