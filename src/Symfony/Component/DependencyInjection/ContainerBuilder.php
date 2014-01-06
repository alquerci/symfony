<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ContainerBuilder is a DI container that provides an API to easily describe services.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_DependencyInjection_ContainerBuilder extends Symfony_Component_DependencyInjection_Container implements Symfony_Component_DependencyInjection_TaggedContainerInterface
{
    /**
     * @var Symfony_Component_DependencyInjection_Extension_ExtensionInterface[]
     */
    private $extensions = array();

    /**
     * @var Symfony_Component_DependencyInjection_Extension_ExtensionInterface[]
     */
    private $extensionsByNs = array();

    /**
     * @var Symfony_Component_DependencyInjection_Definition[]
     */
    private $definitions = array();

    /**
     * @var Symfony_Component_DependencyInjection_Alias[]
     */
    private $aliases = array();

    /**
     * @var Symfony_Component_Config_Resource_ResourceInterface[]
     */
    private $resources = array();

    private $extensionConfigs = array();

    /**
     * @var Symfony_Component_DependencyInjection_Compiler_Compiler
     */
    private $compiler;

    private $trackResources = true;

    /**
     * Sets the track resources flag.
     *
     * If you are not using the loaders and therefore don't want
     * to depend on the Config component, set this flag to false.
     *
     * @param Boolean $track true if you want to track resources, false otherwise
     */
    public function setResourceTracking($track)
    {
        $this->trackResources = (Boolean) $track;
    }

    /**
     * Checks if resources are tracked.
     *
     * @return Boolean true if resources are tracked, false otherwise
     */
    public function isTrackingResources()
    {
        return $this->trackResources;
    }

    /**
     * Registers an extension.
     *
     * @param Symfony_Component_DependencyInjection_Extension_ExtensionInterface $extension An extension instance
     *
     * @api
     */
    public function registerExtension(Symfony_Component_DependencyInjection_Extension_ExtensionInterface $extension)
    {
        $this->extensions[$extension->getAlias()] = $extension;

        if (false !== $extension->getNamespace()) {
            $this->extensionsByNs[$extension->getNamespace()] = $extension;
        }
    }

    /**
     * Returns an extension by alias or namespace.
     *
     * @param string $name An alias or a namespace
     *
     * @return Symfony_Component_DependencyInjection_Extension_ExtensionInterface An extension instance
     *
     * @throws Symfony_Component_DependencyInjection_Exception_LogicException if the extension is not registered
     *
     * @api
     */
    public function getExtension($name)
    {
        if (isset($this->extensions[$name])) {
            return $this->extensions[$name];
        }

        if (isset($this->extensionsByNs[$name])) {
            return $this->extensionsByNs[$name];
        }

        throw new Symfony_Component_DependencyInjection_Exception_LogicException(sprintf('Container extension "%s" is not registered', $name));
    }

    /**
     * Returns all registered extensions.
     *
     * @return Symfony_Component_DependencyInjection_Extension_ExtensionInterface[] An array of ExtensionInterface
     *
     * @api
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Checks if we have an extension.
     *
     * @param string $name The name of the extension
     *
     * @return Boolean If the extension exists
     *
     * @api
     */
    public function hasExtension($name)
    {
        return isset($this->extensions[$name]) || isset($this->extensionsByNs[$name]);
    }

    /**
     * Returns an array of resources loaded to build this configuration.
     *
     * @return Symfony_Component_Config_Resource_ResourceInterface[] An array of resources
     *
     * @api
     */
    public function getResources()
    {
        $seen = array();
        $uniqueResources = array();

        foreach ($this->resources as $key => $resource) {
            $marker = $resource->__toString();
            if (!isset($seen[$marker])) {
                $seen[$marker] = true;
                $uniqueResources[$key] = $resource;
            }
        }

        return $uniqueResources;
    }

    /**
     * Adds a resource for this configuration.
     *
     * @param Symfony_Component_Config_Resource_ResourceInterface $resource A resource instance
     *
     * @return Symfony_Component_DependencyInjection_ContainerBuilder The current instance
     *
     * @api
     */
    public function addResource(Symfony_Component_Config_Resource_ResourceInterface $resource)
    {
        if (!$this->trackResources) {
            return $this;
        }

        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Sets the resources for this configuration.
     *
     * @param Symfony_Component_Config_Resource_ResourceInterface[] $resources An array of resources
     *
     * @return Symfony_Component_DependencyInjection_ContainerBuilder The current instance
     *
     * @api
     */
    public function setResources(array $resources)
    {
        if (!$this->trackResources) {
            return $this;
        }

        $this->resources = $resources;

        return $this;
    }

    /**
     * Adds the object class hierarchy as resources.
     *
     * @param object $object An object instance
     *
     * @return Symfony_Component_DependencyInjection_ContainerBuilder The current instance
     *
     * @api
     */
    public function addObjectResource($object)
    {
        if (!$this->trackResources) {
            return $this;
        }

        $parent = new ReflectionObject($object);
        do {
            $this->addResource(new Symfony_Component_Config_Resource_FileResource($parent->getFileName()));
        } while ($parent = $parent->getParentClass());

        return $this;
    }

    /**
     * Loads the configuration for an extension.
     *
     * @param string $extension The extension alias or namespace
     * @param array  $values    An array of values that customizes the extension
     *
     * @return Symfony_Component_DependencyInjection_ContainerBuilder The current instance
     * @throws Symfony_Component_DependencyInjection_Exception_BadMethodCallException When this ContainerBuilder is frozen
     *
     * @throws LogicException if the container is frozen
     *
     * @api
     */
    public function loadFromExtension($extension, array $values = array())
    {
        if ($this->isFrozen()) {
            throw new Symfony_Component_DependencyInjection_Exception_BadMethodCallException('Cannot load from an extension on a frozen container.');
        }

        $namespace = $this->getExtension($extension)->getAlias();

        $this->extensionConfigs[$namespace][] = $values;

        return $this;
    }

    /**
     * Adds a compiler pass.
     *
     * @param Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass A compiler pass
     * @param string                $type The type of compiler pass
     *
     * @return Symfony_Component_DependencyInjection_ContainerBuilder The current instance
     *
     * @api
     */
    public function addCompilerPass(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $type = Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_BEFORE_OPTIMIZATION)
    {
        if (null === $this->compiler) {
            $this->compiler = new Symfony_Component_DependencyInjection_Compiler_Compiler();
        }

        $this->compiler->addPass($pass, $type);

        $this->addObjectResource($pass);

        return $this;
    }

    /**
     * Returns the compiler pass config which can then be modified.
     *
     * @return Symfony_Component_DependencyInjection_Compiler_PassConfig The compiler pass config
     *
     * @api
     */
    public function getCompilerPassConfig()
    {
        if (null === $this->compiler) {
            $this->compiler = new Symfony_Component_DependencyInjection_Compiler_Compiler();
        }

        return $this->compiler->getPassConfig();
    }

    /**
     * Returns the compiler.
     *
     * @return Symfony_Component_DependencyInjection_Compiler_Compiler The compiler
     *
     * @api
     */
    public function getCompiler()
    {
        if (null === $this->compiler) {
            $this->compiler = new Symfony_Component_DependencyInjection_Compiler_Compiler();
        }

        return $this->compiler;
    }

    /**
     * Returns all Scopes.
     *
     * @return array An array of scopes
     *
     * @api
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Returns all Scope children.
     *
     * @return array An array of scope children.
     *
     * @api
     */
    public function getScopeChildren()
    {
        return $this->scopeChildren;
    }

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     * @param string $scope   The scope
     *
     * @throws Symfony_Component_DependencyInjection_Exception_BadMethodCallException When this ContainerBuilder is frozen
     *
     * @api
     */
    public function set($id, $service, $scope = Symfony_Component_DependencyInjection_ContainerBuilder::SCOPE_CONTAINER)
    {
        $id = strtolower($id);

        if ($this->isFrozen()) {
            // setting a synthetic service on a frozen container is alright
            if (!isset($this->definitions[$id]) || !$this->definitions[$id]->isSynthetic()) {
                throw new Symfony_Component_DependencyInjection_Exception_BadMethodCallException(sprintf('Setting service "%s" on a frozen container is not allowed.', $id));
            }
        }

        unset($this->definitions[$id], $this->aliases[$id]);

        parent::set($id, $service, $scope);
    }

    /**
     * Removes a service definition.
     *
     * @param string $id The service identifier
     *
     * @api
     */
    public function removeDefinition($id)
    {
        unset($this->definitions[strtolower($id)]);
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     *
     * @api
     */
    public function has($id)
    {
        $id = strtolower($id);

        return isset($this->definitions[$id]) || isset($this->aliases[$id]) || parent::has($id);
    }

    /**
     * Gets a service.
     *
     * @param string  $id              The service identifier
     * @param integer $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the service is not defined
     * @throws Symfony_Component_DependencyInjection_Exception_LogicException if the service has a circular reference to itself
     *
     * @see Symfony_Component_DependencyInjection_Reference
     *
     * @api
     */
    public function get($id, $invalidBehavior = Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if ($id instanceof Symfony_Component_DependencyInjection_Reference) {
            $id = $id->__toString();
        }

        $id = strtolower($id);

        try {
            return parent::get($id, Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
        } catch (Symfony_Component_DependencyInjection_Exception_InvalidArgumentException $e) {
            if (isset($this->loading[$id])) {
                throw new Symfony_Component_DependencyInjection_Exception_LogicException(sprintf('The service "%s" has a circular reference to itself.', $id), 0, $e);
            }

            if (!$this->hasDefinition($id) && isset($this->aliases[$id])) {
                return $this->get($this->aliases[$id]->__toString());
            }

            try {
                $definition = $this->getDefinition($id);
            } catch (Symfony_Component_DependencyInjection_Exception_InvalidArgumentException $e) {
                if (Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $invalidBehavior) {
                    return null;
                }

                throw $e;
            }

            $this->loading[$id] = true;

            try {
                $service = $this->createService($definition, $id);
            } catch (Exception $e) {
                unset($this->loading[$id]);
                throw $e;
            }

            unset($this->loading[$id]);

            return $service;
        }
    }

    /**
     * Merges a ContainerBuilder with the current ContainerBuilder configuration.
     *
     * Service definitions overrides the current defined ones.
     *
     * But for parameters, they are overridden by the current ones. It allows
     * the parameters passed to the container constructor to have precedence
     * over the loaded ones.
     *
     * $container = new ContainerBuilder(array('foo' => 'bar'));
     * $loader = new LoaderXXX($container);
     * $loader->load('resource_name');
     * $container->register('foo', new stdClass());
     *
     * In the above example, even if the loaded resource defines a foo
     * parameter, the value will still be 'bar' as defined in the ContainerBuilder
     * constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container The ContainerBuilder instance to merge.
     *
     *
     * @throws Symfony_Component_DependencyInjection_Exception_BadMethodCallException When this ContainerBuilder is frozen
     *
     * @api
     */
    public function merge(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if ($this->isFrozen()) {
            throw new Symfony_Component_DependencyInjection_Exception_BadMethodCallException('Cannot merge on a frozen container.');
        }

        $this->addDefinitions($container->getDefinitions());
        $this->addAliases($container->getAliases());
        $this->getParameterBag()->add($container->getParameterBag()->all());

        if ($this->trackResources) {
            foreach ($container->getResources() as $resource) {
                $this->addResource($resource);
            }
        }

        foreach ($this->extensions as $name => $extension) {
            if (!isset($this->extensionConfigs[$name])) {
                $this->extensionConfigs[$name] = array();
            }

            $this->extensionConfigs[$name] = array_merge($this->extensionConfigs[$name], $container->getExtensionConfig($name));
        }
    }

    /**
     * Returns the configuration array for the given extension.
     *
     * @param string $name The name of the extension
     *
     * @return array An array of configuration
     *
     * @api
     */
    public function getExtensionConfig($name)
    {
        if (!isset($this->extensionConfigs[$name])) {
            $this->extensionConfigs[$name] = array();
        }

        return $this->extensionConfigs[$name];
    }

    /**
     * Prepends a config array to the configs of the given extension.
     *
     * @param string $name    The name of the extension
     * @param array  $config  The config to set
     */
    public function prependExtensionConfig($name, array $config)
    {
        if (!isset($this->extensionConfigs[$name])) {
            $this->extensionConfigs[$name] = array();
        }

        array_unshift($this->extensionConfigs[$name], $config);
    }

    /**
     * Compiles the container.
     *
     * This method passes the container to compiler
     * passes whose job is to manipulate and optimize
     * the container.
     *
     * The main compiler passes roughly do four things:
     *
     *  * The extension configurations are merged;
     *  * Parameter values are resolved;
     *  * The parameter bag is frozen;
     *  * Extension loading is disabled.
     *
     * @api
     */
    public function compile()
    {
        if (null === $this->compiler) {
            $this->compiler = new Symfony_Component_DependencyInjection_Compiler_Compiler();
        }

        if ($this->trackResources) {
            foreach ($this->compiler->getPassConfig()->getPasses() as $pass) {
                $this->addObjectResource($pass);
            }
        }

        $this->compiler->compile($this);

        $this->extensionConfigs = array();

        parent::compile();
    }

    /**
     * Gets all service ids.
     *
     * @return array An array of all defined service ids
     */
    public function getServiceIds()
    {
        return array_unique(array_merge(array_keys($this->getDefinitions()), array_keys($this->aliases), parent::getServiceIds()));
    }

    /**
     * Adds the service aliases.
     *
     * @param array $aliases An array of aliases
     *
     * @api
     */
    public function addAliases(array $aliases)
    {
        foreach ($aliases as $alias => $id) {
            $this->setAlias($alias, $id);
        }
    }

    /**
     * Sets the service aliases.
     *
     * @param array $aliases An array of aliases
     *
     * @api
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = array();
        $this->addAliases($aliases);
    }

    /**
     * Sets an alias for an existing service.
     *
     * @param string        $alias The alias to create
     * @param string|Symfony_Component_DependencyInjection_Alias  $id    The service to alias
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the id is not a string or an Alias
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the alias is for itself
     *
     * @api
     */
    public function setAlias($alias, $id)
    {
        $alias = strtolower($alias);

        if (is_string($id)) {
            $id = new Symfony_Component_DependencyInjection_Alias($id);
        } elseif (!$id instanceof Symfony_Component_DependencyInjection_Alias) {
            throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException('$id must be a string, or an Alias object.');
        }

        if ($alias === strtolower($id->__toString())) {
            throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException('An alias can not reference itself, got a circular reference on "'.$alias.'".');
        }

        unset($this->definitions[$alias]);

        $this->aliases[$alias] = $id;
    }

    /**
     * Removes an alias.
     *
     * @param string $alias The alias to remove
     *
     * @api
     */
    public function removeAlias($alias)
    {
        unset($this->aliases[strtolower($alias)]);
    }

    /**
     * Returns true if an alias exists under the given identifier.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the alias exists, false otherwise
     *
     * @api
     */
    public function hasAlias($id)
    {
        return isset($this->aliases[strtolower($id)]);
    }

    /**
     * Gets all defined aliases.
     *
     * @return Symfony_Component_DependencyInjection_Alias[] An array of aliases
     *
     * @api
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Gets an alias.
     *
     * @param string $id The service identifier
     *
     * @return Symfony_Component_DependencyInjection_Alias An Alias instance
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the alias does not exist
     *
     * @api
     */
    public function getAlias($id)
    {
        $id = strtolower($id);

        if (!$this->hasAlias($id)) {
            throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException(sprintf('The service alias "%s" does not exist.', $id));
        }

        return $this->aliases[$id];
    }

    /**
     * Registers a service definition.
     *
     * This methods allows for simple registration of service definition
     * with a fluid interface.
     *
     * @param string $id    The service identifier
     * @param string $class The service class
     *
     * @return Symfony_Component_DependencyInjection_Definition A Definition instance
     *
     * @api
     */
    public function register($id, $class = null)
    {
        return $this->setDefinition(strtolower($id), new Symfony_Component_DependencyInjection_Definition($class));
    }

    /**
     * Adds the service definitions.
     *
     * @param Symfony_Component_DependencyInjection_Definition[] $definitions An array of service definitions
     *
     * @api
     */
    public function addDefinitions(array $definitions)
    {
        foreach ($definitions as $id => $definition) {
            $this->setDefinition($id, $definition);
        }
    }

    /**
     * Sets the service definitions.
     *
     * @param Symfony_Component_DependencyInjection_Definition[] $definitions An array of service definitions
     *
     * @api
     */
    public function setDefinitions(array $definitions)
    {
        $this->definitions = array();
        $this->addDefinitions($definitions);
    }

    /**
     * Gets all service definitions.
     *
     * @return Symfony_Component_DependencyInjection_Definition[] An array of Definition instances
     *
     * @api
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Sets a service definition.
     *
     * @param string     $id         The service identifier
     * @param Symfony_Component_DependencyInjection_Definition $definition A Definition instance
     *
     * @return Symfony_Component_DependencyInjection_Definition the service definition
     *
     * @throws Symfony_Component_DependencyInjection_Exception_BadMethodCallException When this ContainerBuilder is frozen
     *
     * @api
     */
    public function setDefinition($id, Symfony_Component_DependencyInjection_Definition $definition)
    {
        if ($this->isFrozen()) {
            throw new Symfony_Component_DependencyInjection_Exception_BadMethodCallException('Adding definition to a frozen container is not allowed');
        }

        $id = strtolower($id);

        unset($this->aliases[$id]);

        return $this->definitions[$id] = $definition;
    }

    /**
     * Returns true if a service definition exists under the given identifier.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the service definition exists, false otherwise
     *
     * @api
     */
    public function hasDefinition($id)
    {
        return array_key_exists(strtolower($id), $this->definitions);
    }

    /**
     * Gets a service definition.
     *
     * @param string $id The service identifier
     *
     * @return Symfony_Component_DependencyInjection_Definition A Definition instance
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the service definition does not exist
     *
     * @api
     */
    public function getDefinition($id)
    {
        $id = strtolower($id);

        if (!$this->hasDefinition($id)) {
            throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException(sprintf('The service definition "%s" does not exist.', $id));
        }

        return $this->definitions[$id];
    }

    /**
     * Gets a service definition by id or alias.
     *
     * The method "unaliases" recursively to return a Definition instance.
     *
     * @param string $id The service identifier or alias
     *
     * @return Symfony_Component_DependencyInjection_Definition A Definition instance
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the service definition does not exist
     *
     * @api
     */
    public function findDefinition($id)
    {
        while ($this->hasAlias($id)) {
            $id = (string) $this->getAlias($id)->__toString();
        }

        return $this->getDefinition($id);
    }

    /**
     * Creates a service for a service definition.
     *
     * @param Symfony_Component_DependencyInjection_Definition $definition A service definition instance
     * @param string     $id         The service identifier
     *
     * @return object The service described by the service definition
     *
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException When the scope is inactive
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException When the factory definition is incomplete
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException When the service is a synthetic service
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException When configure callable is not callable
     */
    private function createService(Symfony_Component_DependencyInjection_Definition $definition, $id)
    {
        if ($definition->isSynthetic()) {
            throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf('You have requested a synthetic service ("%s"). The DIC does not know how to construct this service.', $id));
        }

        $parameterBag = $this->getParameterBag();

        if (null !== $definition->getFile()) {
            require_once $parameterBag->resolveValue($definition->getFile());
        }

        $arguments = $this->resolveServices($parameterBag->unescapeValue($parameterBag->resolveValue($definition->getArguments())));

        if (null !== $definition->getFactoryMethod()) {
            if (null !== $definition->getFactoryClass()) {
                $factory = $parameterBag->resolveValue($definition->getFactoryClass());
            } elseif (null !== $definition->getFactoryService()) {
                $factory = $this->get($parameterBag->resolveValue($definition->getFactoryService()));
            } else {
                throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf('Cannot create service "%s" from factory method without a factory service or factory class.', $id));
            }

            $service = call_user_func_array(array($factory, $definition->getFactoryMethod()), $arguments);
        } else {
            $r = new ReflectionClass($parameterBag->resolveValue($definition->getClass()));

            $service = null === $r->getConstructor() ? $r->newInstance() : $r->newInstanceArgs($arguments);
        }

        if (self::SCOPE_PROTOTYPE !== $scope = $definition->getScope()) {
            if (self::SCOPE_CONTAINER !== $scope && !isset($this->scopedServices[$scope])) {
                throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf('You tried to create the "%s" service of an inactive scope.', $id));
            }

            $this->services[$lowerId = strtolower($id)] = $service;

            if (self::SCOPE_CONTAINER !== $scope) {
                $this->scopedServices[$scope][$lowerId] = $service;
            }
        }

        foreach ($definition->getMethodCalls() as $call) {
            $services = self::getServiceConditionals($call[1]);

            $ok = true;
            foreach ($services as $s) {
                if (!$this->has($s)) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                call_user_func_array(array($service, $call[0]), $this->resolveServices($parameterBag->resolveValue($call[1])));
            }
        }

        $properties = $this->resolveServices($parameterBag->resolveValue($definition->getProperties()));
        foreach ($properties as $name => $value) {
            $service->$name = $value;
        }

        if ($callable = $definition->getConfigurator()) {
            if (is_array($callable)) {
                $callable[0] = $callable[0] instanceof Symfony_Component_DependencyInjection_Reference ? $this->get((string) $callable[0]->__toString()) : $parameterBag->resolveValue($callable[0]);
            }

            if (!is_callable($callable)) {
                throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException(sprintf('The configure callable for class "%s" is not a callable.', get_class($service)));
            }

            call_user_func($callable, $service);
        }

        return $service;
    }

    /**
     * Replaces service references by the real service instance.
     *
     * @param mixed $value A value
     *
     * @return mixed The same value with all service references replaced by the real service instances
     */
    public function resolveServices($value)
    {
        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = $this->resolveServices($v);
            }
        } elseif ($value instanceof Symfony_Component_DependencyInjection_Reference) {
            $value = $this->get((string) $value->__toString(), $value->getInvalidBehavior());
        } elseif ($value instanceof Symfony_Component_DependencyInjection_Definition) {
            $value = $this->createService($value, null);
        }

        return $value;
    }

    /**
     * Returns service ids for a given tag.
     *
     * @param string $name The tag name
     *
     * @return array An array of tags
     *
     * @api
     */
    public function findTaggedServiceIds($name)
    {
        $tags = array();
        foreach ($this->getDefinitions() as $id => $definition) {
            if ($definition->getTag($name)) {
                $tags[$id] = $definition->getTag($name);
            }
        }

        return $tags;
    }

    /**
     * Returns all tags the defined services use.
     *
     * @return array An array of tags
     */
    public function findTags()
    {
        $tags = array();
        foreach ($this->getDefinitions() as $id => $definition) {
            $tags = array_merge(array_keys($definition->getTags()), $tags);
        }

        return array_unique($tags);
    }

    /**
     * Returns the Service Conditionals.
     *
     * @param mixed $value An array of conditionals to return.
     *
     * @return array An array of Service conditionals
     */
    public static function getServiceConditionals($value)
    {
        $services = array();

        if (is_array($value)) {
            foreach ($value as $v) {
                $services = array_unique(array_merge($services, self::getServiceConditionals($v)));
            }
        } elseif ($value instanceof Symfony_Component_DependencyInjection_Reference && $value->getInvalidBehavior() === Symfony_Component_DependencyInjection_ContainerInterface::IGNORE_ON_INVALID_REFERENCE) {
            $services[] = (string) $value->__toString();
        }

        return $services;
    }
}
