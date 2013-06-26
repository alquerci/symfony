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
 * InMemoryFactory creates services for the memory provider.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class Symfony_Bundle_SecurityBundle_DependencyInjection_Security_UserProvider_InMemoryFactory implements Symfony_Bundle_SecurityBundle_DependencyInjection_Security_UserProvider_UserProviderFactoryInterface
{
    public function create(Symfony_Component_DependencyInjection_ContainerBuilder $container, $id, $config)
    {
        $definition = $container->setDefinition($id, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.user.provider.in_memory'));

        foreach ($config['users'] as $username => $user) {
            $userId = $id.'_'.$username;

            $container
                ->setDefinition($userId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.user.provider.in_memory.user'))
                ->setArguments(array($username, (string) $user['password'], $user['roles']))
            ;

            $definition->addMethodCall('createUser', array(new Symfony_Component_DependencyInjection_Reference($userId)));
        }
    }

    public function getKey()
    {
        return 'memory';
    }

    public function addConfiguration(Symfony_Component_Config_Definition_Builder_NodeDefinition $node)
    {
        $node
            ->fixXmlConfig('user')
            ->children()
                ->arrayNode('users')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('password')->defaultValue(uniqid())->end()
                            ->arrayNode('roles')
                                ->beforeNormalization()->ifString()->then(create_function('$v', 'return preg_split(\'/\s*,\s*/\', $v);'))->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
