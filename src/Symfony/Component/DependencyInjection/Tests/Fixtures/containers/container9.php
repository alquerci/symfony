<?php

require_once dirname(__FILE__).'/../includes/classes.php';


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addTag('foo', array('foo' => 'foo'))->
    addTag('foo', array('bar' => 'bar'))->
    setFactoryClass('FooClass')->
    setFactoryMethod('getInstance')->
    setArguments(array('foo', new Symfony_Component_DependencyInjection_Reference('foo.baz'), array('%foo%' => 'foo is %foo%', 'foobar' => '%foo%'), true, new Symfony_Component_DependencyInjection_Reference('service_container')))->
    setProperties(array('foo' => 'bar', 'moo' => new Symfony_Component_DependencyInjection_Reference('foo.baz')))->
    addMethodCall('setBar', array(new Symfony_Component_DependencyInjection_Reference('bar')))->
    addMethodCall('initialize')->
    setConfigurator('sc_configure')
;
$container->
    register('bar', 'FooClass')->
    setArguments(array('foo', new Symfony_Component_DependencyInjection_Reference('foo.baz'), new Symfony_Component_DependencyInjection_Parameter('foo_bar')))->
    setScope('container')->
    setConfigurator(array(new Symfony_Component_DependencyInjection_Reference('foo.baz'), 'configure'))
;
$container->
    register('foo.baz', '%baz_class%')->
    setFactoryClass('%baz_class%')->
    setFactoryMethod('getInstance')->
    setConfigurator(array('%baz_class%', 'configureStatic1'))
;
$container->
    register('foo_bar', '%foo_class%')->
    setScope('prototype')
;
$container->getParameterBag()->clear();
$container->getParameterBag()->add(array(
    'baz_class' => 'BazClass',
    'foo_class' => 'FooClass',
    'foo' => 'bar',
));
$container->setAlias('alias_for_foo', 'foo');
$container->
    register('method_call1', 'FooClass')->
    setFile(realpath(dirname(__FILE__).'/../includes/foo.php'))->
    addMethodCall('setBar', array(new Symfony_Component_DependencyInjection_Reference('foo')))->
    addMethodCall('setBar', array(new Symfony_Component_DependencyInjection_Reference('foo2', Symfony_Component_DependencyInjection_ContainerInterface::NULL_ON_INVALID_REFERENCE)))->
    addMethodCall('setBar', array(new Symfony_Component_DependencyInjection_Reference('foo3', Symfony_Component_DependencyInjection_ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))->
    addMethodCall('setBar', array(new Symfony_Component_DependencyInjection_Reference('foobaz', Symfony_Component_DependencyInjection_ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))
;
$container->
    register('factory_service', 'Bar')->
    setFactoryService('foo.baz')->
    setFactoryMethod('getInstance')
;

$container
    ->register('foo_with_inline', 'Foo')
    ->addMethodCall('setBar', array(new Symfony_Component_DependencyInjection_Reference('inlined')))
;
$container
    ->register('inlined', 'Bar')
    ->setProperty('pub', 'pub')
    ->addMethodCall('setBaz', array(new Symfony_Component_DependencyInjection_Reference('baz')))
    ->setPublic(false)
;
$container
    ->register('baz', 'Baz')
    ->addMethodCall('setFoo', array(new Symfony_Component_DependencyInjection_Reference('foo_with_inline')))
;

return $container;
