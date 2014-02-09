<?php


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new Symfony_Component_DependencyInjection_Reference('bar'))
;
$container->
    register('bar', 'BarClass')
;
$container->compile();

return $container;
