<?php


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();

$factoryDefinition = new Symfony_Component_DependencyInjection_Definition('BarClassFactory');
$container->setDefinition('barFactory', $factoryDefinition);

$definition = new Symfony_Component_DependencyInjection_Definition();
$definition->setFactoryService('barFactory');
$definition->setFactoryMethod('createBarClass');
$container->setDefinition('bar', $definition);

return $container;

class BarClass
{
    public $foo;

    public function setBar($foo)
    {
        $this->foo = $foo;
    }
}

class BarClassFactory
{
    public function createBarClass()
    {
        return new BarClass();
    }
}
