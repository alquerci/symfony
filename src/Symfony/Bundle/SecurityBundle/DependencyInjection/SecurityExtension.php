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
 * SecurityExtension.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Bundle_SecurityBundle_DependencyInjection_SecurityExtension extends Symfony_Component_HttpKernel_DependencyInjection_Extension
{
    private $requestMatchers = array();
    private $contextListeners = array();
    private $listenerPositions = array('pre_auth', 'form', 'http', 'remember_me');
    private $factories = array();
    private $userProviderFactories = array();

    public function __construct()
    {
        foreach ($this->listenerPositions as $position) {
            $this->factories[$position] = array();
        }
    }

    public function load(array $configs, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!array_filter($configs)) {
            return;
        }

        $mainConfig = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($mainConfig, $configs);

        // load services
        $loader = new Symfony_Component_DependencyInjection_Loader_XmlFileLoader($container, new Symfony_Component_Config_FileLocator(dirname(__FILE__).'/../Resources/config'));
        $loader->load('security.xml');
        $loader->load('security_listeners.xml');
        $loader->load('security_rememberme.xml');
        $loader->load('templating_php.xml');
        // TODO $loader->load('templating_twig.xml');
        // TODO $loader->load('collectors.xml');

        // set some global scalars
        $container->setParameter('security.access.denied_url', $config['access_denied_url']);
        $container->setParameter('security.authentication.manager.erase_credentials', $config['erase_credentials']);
        $container->setParameter('security.authentication.session_strategy.strategy', $config['session_fixation_strategy']);
        $container
            ->getDefinition('security.access.decision_manager')
            ->addArgument($config['access_decision_manager']['strategy'])
            ->addArgument($config['access_decision_manager']['allow_if_all_abstain'])
            ->addArgument($config['access_decision_manager']['allow_if_equal_granted_denied'])
        ;
        $container->setParameter('security.access.always_authenticate_before_granting', $config['always_authenticate_before_granting']);
        $container->setParameter('security.authentication.hide_user_not_found', $config['hide_user_not_found']);

        $this->createFirewalls($config, $container);
        $this->createAuthorization($config, $container);
        $this->createRoleHierarchy($config, $container);

        if ($config['encoders']) {
            $this->createEncoders($config['encoders'], $container);
        }

        // load ACL
        if (isset($config['acl'])) {
            // TODO $this->aclLoad($config['acl'], $container);
        }

        // add some required classes for compilation
        $this->addClassesToCompile(array(
            'Symfony_Component_Security_Http_Firewall',
            'Symfony_Component_Security_Core_SecurityContext',
            'Symfony_Component_Security_Core_User_UserProviderInterface',
            'Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager',
            'Symfony_Component_Security_Core_Authorization_AccessDecisionManager',
            'Symfony_Component_Security_Core_Authorization_Voter_VoterInterface',
            'Symfony_Bundle_SecurityBundle_Security_FirewallMap',
            'Symfony_Bundle_SecurityBundle_Security_FirewallContext',
            'Symfony_Component_HttpFoundation_RequestMatcher',
        ));
    }

    private function aclLoad($config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_XmlFileLoader($container, new Symfony_Component_Config_FileLocator(dirname(__FILE__).'/../Resources/config'));
        $loader->load('security_acl.xml');

        if (isset($config['cache']['id'])) {
            $container->setAlias('security.acl.cache', $config['cache']['id']);
        }
        $container->getDefinition('security.acl.voter.basic_permissions')->addArgument($config['voter']['allow_if_object_identity_unavailable']);

        // custom ACL provider
        if (isset($config['provider'])) {
            $container->setAlias('security.acl.provider', $config['provider']);

            return;
        }

        $this->configureDbalAclProvider($config, $container, $loader);
    }

    private function configureDbalAclProvider(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, $loader)
    {
        $loader->load('security_acl_dbal.xml');

        if (null !== $config['connection']) {
            $container->setAlias('security.acl.dbal.connection', sprintf('doctrine.dbal.%s_connection', $config['connection']));
        }

        $container
            ->getDefinition('security.acl.dbal.schema_listener')
            ->addTag('doctrine.event_listener', array(
                'connection' => $config['connection'],
                'event'      => 'postGenerateSchema',
                'lazy'       => true
            ))
        ;

        $container->getDefinition('security.acl.cache.doctrine')->addArgument($config['cache']['prefix']);

        $container->setParameter('security.acl.dbal.class_table_name', $config['tables']['class']);
        $container->setParameter('security.acl.dbal.entry_table_name', $config['tables']['entry']);
        $container->setParameter('security.acl.dbal.oid_table_name', $config['tables']['object_identity']);
        $container->setParameter('security.acl.dbal.oid_ancestors_table_name', $config['tables']['object_identity_ancestors']);
        $container->setParameter('security.acl.dbal.sid_table_name', $config['tables']['security_identity']);
    }

    /**
     * Loads the web configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     */

    private function createRoleHierarchy($config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!isset($config['role_hierarchy'])) {
            $container->removeDefinition('security.access.role_hierarchy_voter');

            return;
        }

        $container->setParameter('security.role_hierarchy.roles', $config['role_hierarchy']);
        $container->removeDefinition('security.access.simple_role_voter');
    }

    private function createAuthorization($config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$config['access_control']) {
            return;
        }

        $this->addClassesToCompile(array(
            'Symfony_Component_Security_Http_AccessMap',
        ));

        foreach ($config['access_control'] as $access) {
            $matcher = $this->createRequestMatcher(
                $container,
                $access['path'],
                $access['host'],
                $access['methods'],
                $access['ip']
            );

            $container->getDefinition('security.access_map')
                      ->addMethodCall('add', array($matcher, $access['roles'], $access['requires_channel']));
        }
    }

    private function createFirewalls($config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!isset($config['firewalls'])) {
            return;
        }

        $firewalls = $config['firewalls'];
        $providerIds = $this->createUserProviders($config, $container);

        // make the ContextListener aware of the configured user providers
        $definition = $container->getDefinition('security.context_listener');
        $arguments = $definition->getArguments();
        $userProviders = array();
        foreach ($providerIds as $userProviderId) {
            $userProviders[] = new Symfony_Component_DependencyInjection_Reference($userProviderId);
        }
        $arguments[1] = $userProviders;
        $definition->setArguments($arguments);

        // load firewall map
        $mapDef = $container->getDefinition('security.firewall.map');
        $map = $authenticationProviders = array();
        foreach ($firewalls as $name => $firewall) {
            list($matcher, $listeners, $exceptionListener) = $this->createFirewall($container, $name, $firewall, $authenticationProviders, $providerIds);

            $contextId = 'security.firewall.map.context.'.$name;
            $context = $container->setDefinition($contextId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.firewall.context'));
            $context
                ->replaceArgument(0, $listeners)
                ->replaceArgument(1, $exceptionListener)
            ;
            $map[$contextId] = $matcher;
        }
        $mapDef->replaceArgument(1, $map);

        // add authentication providers to authentication manager
        $authenticationProviders = array_map(create_function('$id', '
            return new Symfony_Component_DependencyInjection_Reference($id);
        '), array_values(array_unique($authenticationProviders)));
        $container
            ->getDefinition('security.authentication.manager')
            ->replaceArgument(0, $authenticationProviders)
        ;
    }

    private function createFirewall(Symfony_Component_DependencyInjection_ContainerBuilder $container, $id, $firewall, &$authenticationProviders, $providerIds)
    {
        // Matcher
        $i = 0;
        $matcher = null;
        if (isset($firewall['request_matcher'])) {
            $matcher = new Symfony_Component_DependencyInjection_Reference($firewall['request_matcher']);
        } elseif (isset($firewall['pattern'])) {
            $matcher = $this->createRequestMatcher($container, $firewall['pattern']);
        }

        // Security disabled?
        if (false === $firewall['security']) {
            return array($matcher, array(), null);
        }

        // Provider id (take the first registered provider if none defined)
        if (isset($firewall['provider'])) {
            $defaultProvider = $this->getUserProviderId($firewall['provider']);
        } else {
            $defaultProvider = reset($providerIds);
        }

        // Register listeners
        $listeners = array();

        // Channel listener
        $listeners[] = new Symfony_Component_DependencyInjection_Reference('security.channel_listener');

        // Context serializer listener
        if (false === $firewall['stateless']) {
            $contextKey = $id;
            if (isset($firewall['context'])) {
                $contextKey = $firewall['context'];
            }

            $listeners[] = new Symfony_Component_DependencyInjection_Reference($this->createContextListener($container, $contextKey));
        }

        // Logout listener
        if (isset($firewall['logout'])) {
            $listenerId = 'security.logout_listener.'.$id;
            $listener = $container->setDefinition($listenerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.logout_listener'));
            $listener->replaceArgument(3, array(
                'csrf_parameter' => $firewall['logout']['csrf_parameter'],
                'intention'      => $firewall['logout']['intention'],
                'logout_path'    => $firewall['logout']['path'],
            ));
            $listeners[] = new Symfony_Component_DependencyInjection_Reference($listenerId);

            // add logout success handler
            if (isset($firewall['logout']['success_handler'])) {
                $logoutSuccessHandlerId = $firewall['logout']['success_handler'];
            } else {
                $logoutSuccessHandlerId = 'security.logout.success_handler.'.$id;
                $logoutSuccessHandler = $container->setDefinition($logoutSuccessHandlerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.logout.success_handler'));
                $logoutSuccessHandler->replaceArgument(1, $firewall['logout']['target']);
            }
            $listener->replaceArgument(2, new Symfony_Component_DependencyInjection_Reference($logoutSuccessHandlerId));

            // add CSRF provider
            if (isset($firewall['logout']['csrf_provider'])) {
                $listener->addArgument(new Symfony_Component_DependencyInjection_Reference($firewall['logout']['csrf_provider']));
            }

            // add session logout handler
            if (true === $firewall['logout']['invalidate_session'] && false === $firewall['stateless']) {
                $listener->addMethodCall('addHandler', array(new Symfony_Component_DependencyInjection_Reference('security.logout.handler.session')));
            }

            // add cookie logout handler
            if (count($firewall['logout']['delete_cookies']) > 0) {
                $cookieHandlerId = 'security.logout.handler.cookie_clearing.'.$id;
                $cookieHandler = $container->setDefinition($cookieHandlerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.logout.handler.cookie_clearing'));
                $cookieHandler->addArgument($firewall['logout']['delete_cookies']);

                $listener->addMethodCall('addHandler', array(new Symfony_Component_DependencyInjection_Reference($cookieHandlerId)));
            }

            // add custom handlers
            foreach ($firewall['logout']['handlers'] as $handlerId) {
                $listener->addMethodCall('addHandler', array(new Symfony_Component_DependencyInjection_Reference($handlerId)));
            }

            // register with LogoutUrlHelper
            $container
                ->getDefinition('templating.helper.logout_url')
                ->addMethodCall('registerListener', array(
                    $id,
                    $firewall['logout']['path'],
                    $firewall['logout']['intention'],
                    $firewall['logout']['csrf_parameter'],
                    isset($firewall['logout']['csrf_provider']) ? new Symfony_Component_DependencyInjection_Reference($firewall['logout']['csrf_provider']) : null,
                ))
            ;
        }

        // Authentication listeners
        list($authListeners, $defaultEntryPoint) = $this->createAuthenticationListeners($container, $id, $firewall, $authenticationProviders, $defaultProvider);

        $listeners = array_merge($listeners, $authListeners);

        // Access listener
        $listeners[] = new Symfony_Component_DependencyInjection_Reference('security.access_listener');

        // Switch user listener
        if (isset($firewall['switch_user'])) {
            $listeners[] = new Symfony_Component_DependencyInjection_Reference($this->createSwitchUserListener($container, $id, $firewall['switch_user'], $defaultProvider));
        }

        // Determine default entry point
        if (isset($firewall['entry_point'])) {
            $defaultEntryPoint = $firewall['entry_point'];
        }

        // Exception listener
        $exceptionListener = new Symfony_Component_DependencyInjection_Reference($this->createExceptionListener($container, $firewall, $id, $defaultEntryPoint));

        return array($matcher, $listeners, $exceptionListener);
    }

    private function createContextListener($container, $contextKey)
    {
        if (isset($this->contextListeners[$contextKey])) {
            return $this->contextListeners[$contextKey];
        }

        $listenerId = 'security.context_listener.'.count($this->contextListeners);
        $listener = $container->setDefinition($listenerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.context_listener'));
        $listener->replaceArgument(2, $contextKey);

        return $this->contextListeners[$contextKey] = $listenerId;
    }

    private function createAuthenticationListeners($container, $id, $firewall, &$authenticationProviders, $defaultProvider)
    {
        $listeners = array();
        $hasListeners = false;
        $defaultEntryPoint = null;

        foreach ($this->listenerPositions as $position) {
            foreach ($this->factories[$position] as $factory) {
                $key = str_replace('-', '_', $factory->getKey());

                if (isset($firewall[$key])) {
                    $userProvider = isset($firewall[$key]['provider']) ? $this->getUserProviderId($firewall[$key]['provider']) : $defaultProvider;

                    list($provider, $listenerId, $defaultEntryPoint) = $factory->create($container, $id, $firewall[$key], $userProvider, $defaultEntryPoint);

                    $listeners[] = new Symfony_Component_DependencyInjection_Reference($listenerId);
                    $authenticationProviders[] = $provider;
                    $hasListeners = true;
                }
            }
        }

        // Anonymous
        if (isset($firewall['anonymous'])) {
            $listenerId = 'security.authentication.listener.anonymous.'.$id;
            $container
                ->setDefinition($listenerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.authentication.listener.anonymous'))
                ->replaceArgument(1, $firewall['anonymous']['key'])
            ;

            $listeners[] = new Symfony_Component_DependencyInjection_Reference($listenerId);

            $providerId = 'security.authentication.provider.anonymous.'.$id;
            $container
                ->setDefinition($providerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.authentication.provider.anonymous'))
                ->replaceArgument(0, $firewall['anonymous']['key'])
            ;

            $authenticationProviders[] = $providerId;
            $hasListeners = true;
        }

        if (false === $hasListeners) {
            throw new LogicException(sprintf('No authentication listener registered for firewall "%s".', $id));
        }

        return array($listeners, $defaultEntryPoint);
    }

    private function createEncoders($encoders, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $encoderMap = array();
        foreach ($encoders as $class => $encoder) {
            $encoderMap[$class] = $this->createEncoder($encoder, $container);
        }

        $container
            ->getDefinition('security.encoder_factory.generic')
            ->setArguments(array($encoderMap))
        ;
    }

    private function createEncoder($config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        // a custom encoder service
        if (isset($config['id'])) {
            return new Symfony_Component_DependencyInjection_Reference($config['id']);
        }

        // plaintext encoder
        if ('plaintext' === $config['algorithm']) {
            $arguments = array($config['ignore_case']);

            return array(
                'class' => new Symfony_Component_DependencyInjection_Parameter('security.encoder.plain.class'),
                'arguments' => $arguments,
            );
        }

        // pbkdf2 encoder
        if ('pbkdf2' === $config['algorithm']) {
            $arguments = array(
                $config['hash_algorithm'],
                $config['encode_as_base64'],
                $config['iterations'],
                $config['key_length'],
            );

            return array(
                'class' => new Symfony_Component_DependencyInjection_Parameter('security.encoder.pbkdf2.class'),
                'arguments' => $arguments,
            );
        }

        // bcrypt encoder
        if ('bcrypt' === $config['algorithm']) {
            $arguments = array(
                new Symfony_Component_DependencyInjection_Reference('security.secure_random'),
                $config['cost'],
            );

            return array(
                'class' => new Symfony_Component_DependencyInjection_Parameter('security.encoder.bcrypt.class'),
                'arguments' => $arguments,
            );
        }

        // message digest encoder
        $arguments = array(
            $config['algorithm'],
            $config['encode_as_base64'],
            $config['iterations'],
        );

        return array(
            'class' => new Symfony_Component_DependencyInjection_Parameter('security.encoder.digest.class'),
            'arguments' => $arguments,
        );
    }

    // Parses user providers and returns an array of their ids
    private function createUserProviders($config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $providerIds = array();
        foreach ($config['providers'] as $name => $provider) {
            $id = $this->createUserDaoProvider($name, $provider, $container);
            $providerIds[] = $id;
        }

        return $providerIds;
    }

    // Parses a <provider> tag and returns the id for the related user provider service
    private function createUserDaoProvider($name, $provider, Symfony_Component_DependencyInjection_ContainerBuilder $container, $master = true)
    {
        $name = $this->getUserProviderId(strtolower($name));

        foreach ($this->userProviderFactories as $factory) {
            $key = str_replace('-', '_', $factory->getKey());

            if (!empty($provider[$key])) {
                $factory->create($container, $name, $provider[$key]);

                return $name;
            }
        }

        // Existing DAO service provider
        if (isset($provider['id'])) {
            $container->setAlias($name, new Symfony_Component_DependencyInjection_Alias($provider['id'], false));

            return $provider['id'];
        }

        // Chain provider
        if (isset($provider['chain'])) {
            $providers = array();
            foreach ($provider['chain']['providers'] as $providerName) {
                $providers[] = new Symfony_Component_DependencyInjection_Reference($this->getUserProviderId(strtolower($providerName)));
            }

            $container
                ->setDefinition($name, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.user.provider.chain'))
                ->addArgument($providers)
            ;

            return $name;
        }

        // Doctrine Entity DAO provider
        if (isset($provider['entity'])) {
            $container
                ->setDefinition($name, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.user.provider.entity'))
                ->addArgument($provider['entity']['class'])
                ->addArgument($provider['entity']['property'])
            ;

            return $name;
        }

        // In-memory DAO provider
        $definition = $container->setDefinition($name, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.user.provider.in_memory'));
        foreach ($provider['users'] as $username => $user) {
            $userId = $name.'_'.$username;

            $container
                ->setDefinition($userId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.user.provider.in_memory.user'))
                ->setArguments(array($username, (string) $user['password'], $user['roles']))
            ;

            $definition->addMethodCall('createUser', array(new Symfony_Component_DependencyInjection_Reference($userId)));
        }

        return $name;
    }

    private function getUserProviderId($name)
    {
        return 'security.user.provider.concrete.'.$name;
    }

    private function createExceptionListener($container, $config, $id, $defaultEntryPoint)
    {
        $exceptionListenerId = 'security.exception_listener.'.$id;
        $listener = $container->setDefinition($exceptionListenerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.exception_listener'));
        $listener->replaceArgument(3, $id);
        $listener->replaceArgument(4, null === $defaultEntryPoint ? null : new Symfony_Component_DependencyInjection_Reference($defaultEntryPoint));

        // access denied handler setup
        if (isset($config['access_denied_handler'])) {
            $listener->replaceArgument(6, new Symfony_Component_DependencyInjection_Reference($config['access_denied_handler']));
        } elseif (isset($config['access_denied_url'])) {
            $listener->replaceArgument(5, $config['access_denied_url']);
        }

        return $exceptionListenerId;
    }

    private function createSwitchUserListener($container, $id, $config, $defaultProvider)
    {
        $userProvider = isset($config['provider']) ? $this->getUserProviderId($config['provider']) : $defaultProvider;

        $switchUserListenerId = 'security.authentication.switchuser_listener.'.$id;
        $listener = $container->setDefinition($switchUserListenerId, new Symfony_Component_DependencyInjection_DefinitionDecorator('security.authentication.switchuser_listener'));
        $listener->replaceArgument(1, new Symfony_Component_DependencyInjection_Reference($userProvider));
        $listener->replaceArgument(3, $id);
        $listener->replaceArgument(6, $config['parameter']);
        $listener->replaceArgument(7, $config['role']);

        return $switchUserListenerId;
    }

    private function createRequestMatcher($container, $path = null, $host = null, $methods = array(), $ip = null, array $attributes = array())
    {
        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);

        if (isset($this->requestMatchers[$id])) {
            return $this->requestMatchers[$id];
        }

        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip, $attributes);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container
            ->register($id, '%security.matcher.class%')
            ->setPublic(false)
            ->setArguments($arguments)
        ;

        return $this->requestMatchers[$id] = new Symfony_Component_DependencyInjection_Reference($id);
    }

    public function addSecurityListenerFactory(Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_SecurityFactoryInterface $factory)
    {
        $this->factories[$factory->getPosition()][] = $factory;
    }

    public function addUserProviderFactory(Symfony_Bundle_SecurityBundle_DependencyInjection_Security_UserProvider_UserProviderFactoryInterface $factory)
    {
        $this->userProviderFactories[] = $factory;
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return dirname(__FILE__).'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/security';
    }

    public function getConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        // first assemble the factories
        return new Symfony_Bundle_SecurityBundle_DependencyInjection_MainConfiguration($this->factories, $this->userProviderFactories);
    }
}
