<?php


$container = new Symfony_Component_DependencyInjection_ContainerBuilder();
$container->setParameter('cla', 'Fo');
$container->setParameter('ss', 'Class');

$definition = new Symfony_Component_DependencyInjection_Definition('%cla%o%ss%');
$container->setDefinition('foo', $definition);

return $container;

if (!class_exists('FooClass')) {
    class FooClass
    {
        public $bar;

        public function setBar($bar)
        {
            $this->bar = $bar;
        }
    }
}
