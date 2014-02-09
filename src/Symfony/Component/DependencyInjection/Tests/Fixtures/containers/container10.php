<?php

require_once __DIR__.'/../includes/classes.php';


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new Symfony_Component_DependencyInjection_Reference('bar'))
;

return $container;
