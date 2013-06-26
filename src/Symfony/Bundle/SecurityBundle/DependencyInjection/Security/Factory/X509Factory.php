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
 * X509Factory creates services for X509 certificate authentication.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_X509Factory implements Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_SecurityFactoryInterface
{
    public function create(Symfony_Component_DependencyInjection_ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.pre_authenticated.'.$id;
        $container
            ->setDefinition($providerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.authentication.provider.pre_authenticated'))
            ->replaceArgument(0, new Symfony_Component_DependencyInjection_Reference($userProvider))
            ->addArgument($id)
        ;

        // listener
        $listenerId = 'security.authentication.listener.x509.'.$id;
        $listener = $container->setDefinition($listenerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.authentication.listener.x509'));
        $listener->replaceArgument(2, $id);
        $listener->replaceArgument(3, $config['user']);
        $listener->replaceArgument(4, $config['credentials']);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'x509';
    }

    public function addConfiguration(Symfony_Component_Config_Definition_Builder_NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('provider')->end()
                ->scalarNode('user')->defaultValue('SSL_CLIENT_S_DN_Email')->end()
                ->scalarNode('credentials')->defaultValue('SSL_CLIENT_S_DN')->end()
            ->end()
        ;
    }
}
