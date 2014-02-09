<?php


class ProjectExtension implements Symfony_Component_DependencyInjection_Extension_ExtensionInterface
{
    public function load(array $configs, Symfony_Component_DependencyInjection_ContainerBuilder $configuration)
    {
        $config = call_user_func_array('array_merge', $configs);

        $configuration->setDefinition('project.service.bar', new Symfony_Component_DependencyInjection_Definition('FooClass'));
        $configuration->setParameter('project.parameter.bar', isset($config['foo']) ? $config['foo'] : 'foobar');

        $configuration->setDefinition('project.service.foo', new Symfony_Component_DependencyInjection_Definition('FooClass'));
        $configuration->setParameter('project.parameter.foo', isset($config['foo']) ? $config['foo'] : 'foobar');

        return $configuration;
    }

    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getNamespace()
    {
        return 'http://www.example.com/schema/project';
    }

    public function getAlias()
    {
        return 'project';
    }

    public function getConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        return null;
    }
}
