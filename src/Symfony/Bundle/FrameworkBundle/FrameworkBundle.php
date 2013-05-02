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
 * Bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_FrameworkBundle extends Symfony_Component_HttpKernel_Bundle_Bundle
{
    public function build(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        parent::build($container);

        $container->addScope(new Symfony_Component_DependencyInjection_Scope('request'));

        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_RegisterKernelListenersPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);


        $container->register('service_container')
            ->setSynthetic(true)
        ;

        $container->register('kernel')
            ->setSynthetic(true)
        ;

        $container->register('request')
            ->setSynthetic(true)
            ->setScope('request')
        ;

        $container->register('event_dispatcher')
            ->setClass('Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('service_container'))
        ;

        $container->register('file_locator')
            ->setClass('Symfony_Component_HttpKernel_Config_FileLocator')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('kernel'))
            ->addArgument('%kernel.root_dir%/Resources')
        ;
    }
}
