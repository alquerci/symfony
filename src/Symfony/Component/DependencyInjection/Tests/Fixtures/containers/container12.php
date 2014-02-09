<?php


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();
$container->
    register('foo', 'FooClass\\Foo')->
    addArgument('foo<>&bar')->
    addTag('foo"bar\\bar', array('foo' => 'foo"barřž€'))
;

return $container;
