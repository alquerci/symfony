<?php


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new Symfony_Component_DependencyInjection_Definition('BarClass', array(new Symfony_Component_DependencyInjection_Definition('BazClass'))))
;

return $container;
