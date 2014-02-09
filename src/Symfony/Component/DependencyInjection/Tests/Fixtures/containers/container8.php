<?php


$container = new Symfony_Component_DependencyInjection_ContainerBuilder(new Symfony_Component_DependencyInjection_ParameterBag_ParameterBag(array(
    'FOO'    => '%baz%',
    'baz'    => 'bar',
    'bar'    => 'foo is %%foo bar',
    'escape' => '@escapeme',
    'values' => array(true, false, null, 0, 1000.3, 'true', 'false', 'null'),
)));

return $container;
