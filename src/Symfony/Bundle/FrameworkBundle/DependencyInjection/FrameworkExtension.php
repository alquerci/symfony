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
 * FrameworkExtension.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_FrameworkExtension extends Symfony_Component_HttpKernel_DependencyInjection_Extension
{
    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function load(array $configs, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_XmlFileLoader($container, new Symfony_Component_Config_FileLocator(dirname(__FILE__).'/../Resources/config'));

        $loader->load('web.xml');
        $loader->load('services.xml');
        $loader->load('fragment_renderer.xml');

        // A translator must always be registered (as support is included by
        // default in the Form component). If disabled, an identity translator
        // will be used and everything will still work as expected.
        $loader->load('translation.xml');

        if ($container->getParameter('kernel.debug')) {
            // TODO line above remove after load debug.xml
            $container->setParameter('debug.container.dump', '%kernel.cache_dir%/%kernel.container_class%.xml');
            // TODO $loader->load('debug.xml');

            // only HttpKernel needs the debug event dispatcher
            // TODO $definition = $container->findDefinition('http_kernel');
            // TODO $arguments = $definition->getArguments();
            // TODO $arguments[0] = new Symfony_Component_DependencyInjection_Reference('debug.event_dispatcher');
            // TODO $arguments[2] = new Symfony_Component_DependencyInjection_Reference('debug.controller_resolver');
            // TODO $definition->setArguments($arguments);
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['secret'])) {
            $container->setParameter('kernel.secret', $config['secret']);
        }

        $container->setParameter('kernel.trusted_proxies', $config['trusted_proxies']);

        // @deprecated, to be removed in 2.3
        $container->setParameter('kernel.trust_proxy_headers', $config['trust_proxy_headers']);

        $container->setParameter('kernel.default_locale', $config['default_locale']);

        if (!empty($config['test'])) {
            $loader->load('test.xml');
        }

        if (isset($config['session'])) {
            $this->registerSessionConfiguration($config['session'], $container, $loader);
        }

        if ($this->isConfigEnabled($container, $config['form'])) {
            // TODO $this->registerFormConfiguration($config, $container, $loader);
            $config['validation']['enabled'] = true;
        }

        if (isset($config['templating'])) {
            $this->registerTemplatingConfiguration($config['templating'], $config['ide'], $container, $loader);
        }

        // TODO $this->registerValidationConfiguration($config['validation'], $container, $loader);
        $this->registerEsiConfiguration($config['esi'], $container, $loader);
        $this->registerFragmentsConfiguration($config['fragments'], $container, $loader);
        // TODO $this->registerProfilerConfiguration($config['profiler'], $container, $loader);
        $this->registerTranslatorConfiguration($config['translator'], $container);

        if (isset($config['router'])) {
            $this->registerRouterConfiguration($config['router'], $container, $loader);
        }

        // TODO $this->registerAnnotationsConfiguration($config['annotations'], $container, $loader);

        $this->addClassesToCompile(array(
            'Symfony_Component_HttpFoundation_ParameterBag',
            'Symfony_Component_HttpFoundation_HeaderBag',
            'Symfony_Component_HttpFoundation_FileBag',
            'Symfony_Component_HttpFoundation_ServerBag',
            'Symfony_Component_HttpFoundation_Request',
            'Symfony_Component_HttpFoundation_Response',
            'Symfony_Component_HttpFoundation_ResponseHeaderBag',

            'Symfony_Component_Config_FileLocator',

            'Symfony_Component_EventDispatcher_Event',
            'Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher',

            'Symfony_Component_HttpKernel_EventListener_ResponseListener',
            'Symfony_Component_HttpKernel_EventListener_RouterListener',
            'Symfony_Component_HttpKernel_Controller_ControllerResolver',
            'Symfony_Component_HttpKernel_Event_KernelEvent',
            'Symfony_Component_HttpKernel_Event_FilterControllerEvent',
            'Symfony_Component_HttpKernel_Event_FilterResponseEvent',
            'Symfony_Component_HttpKernel_Event_GetResponseEvent',
            'Symfony_Component_HttpKernel_Event_GetResponseForControllerResultEvent',
            'Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent',
            'Symfony_Component_HttpKernel_KernelEvents',
            'Symfony_Component_HttpKernel_Config_FileLocator',

            'Symfony_Bundle_FrameworkBundle_Controller_ControllerNameParser',
            'Symfony_Bundle_FrameworkBundle_Controller_ControllerResolver',
            // Cannot be included because annotations will parse the big compiled class file
            // 'Symfony_Bundle_FrameworkBundle_Controller_Controller',
        ));
    }

    /**
     * Loads Form configuration.
     *
     * @param array            $config    A configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     *
     * @throws LogicException
     */
    private function registerFormConfiguration($config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        $loader->load('form.xml');
        if ($this->isConfigEnabled($container, $config['csrf_protection'])) {
            if (!isset($config['session'])) {
                throw new LogicException('CSRF protection needs that sessions are enabled.');
            }
            if (!isset($config['secret'])) {
                throw new LogicException('CSRF protection needs a secret to be set.');
            }
            $loader->load('form_csrf.xml');

            $container->setParameter('form.type_extension.csrf.enabled', true);
            $container->setParameter('form.type_extension.csrf.field_name', $config['csrf_protection']['field_name']);
        } else {
            $container->setParameter('form.type_extension.csrf.enabled', false);
        }
    }

    /**
     * Loads the ESI configuration.
     *
     * @param array            $config    An ESI configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerEsiConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('esi.xml');
    }

    /**
     * Loads the fragments configuration.
     *
     * @param array            $config    A fragments configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerFragmentsConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('fragment_listener.xml');
        $container->setParameter('fragment.path', $config['path']);
    }

    /**
     * Loads the profiler configuration.
     *
     * @param array            $config    A profiler configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     *
     * @throws LogicException
     */
    private function registerProfilerConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        $loader->load('profiling.xml');
        $loader->load('collectors.xml');

        $container->setParameter('profiler_listener.only_exceptions', $config['only_exceptions']);
        $container->setParameter('profiler_listener.only_master_requests', $config['only_master_requests']);

        // Choose storage class based on the DSN
        $supported = array(
            'sqlite'    => 'Symfony\Component\HttpKernel\Profiler\SqliteProfilerStorage',
            'mysql'     => 'Symfony\Component\HttpKernel\Profiler\MysqlProfilerStorage',
            'file'      => 'Symfony\Component\HttpKernel\Profiler\FileProfilerStorage',
            'mongodb'   => 'Symfony\Component\HttpKernel\Profiler\MongoDbProfilerStorage',
            'memcache'  => 'Symfony\Component\HttpKernel\Profiler\MemcacheProfilerStorage',
            'memcached' => 'Symfony\Component\HttpKernel\Profiler\MemcachedProfilerStorage',
            'redis'     => 'Symfony\Component\HttpKernel\Profiler\RedisProfilerStorage',
        );
        list($class, ) = explode(':', $config['dsn'], 2);
        if (!isset($supported[$class])) {
            throw new LogicException(sprintf('Driver "%s" is not supported for the profiler.', $class));
        }

        $container->setParameter('profiler.storage.dsn', $config['dsn']);
        $container->setParameter('profiler.storage.username', $config['username']);
        $container->setParameter('profiler.storage.password', $config['password']);
        $container->setParameter('profiler.storage.lifetime', $config['lifetime']);

        $container->getDefinition('profiler.storage')->setClass($supported[$class]);

        if (isset($config['matcher'])) {
            if (isset($config['matcher']['service'])) {
                $container->setAlias('profiler.request_matcher', $config['matcher']['service']);
            } elseif (isset($config['matcher']['ip']) || isset($config['matcher']['path'])) {
                $definition = $container->register('profiler.request_matcher', 'Symfony\\Component\\HttpFoundation\\RequestMatcher');
                $definition->setPublic(false);

                if (isset($config['matcher']['ip'])) {
                    $definition->addMethodCall('matchIp', array($config['matcher']['ip']));
                }

                if (isset($config['matcher']['path'])) {
                    $definition->addMethodCall('matchPath', array($config['matcher']['path']));
                }
            }
        }

        if (!$this->isConfigEnabled($container, $config)) {
            $container->getDefinition('profiler')->addMethodCall('disable', array());
        }
    }

    /**
     * Loads the router configuration.
     *
     * @param array            $config    A router configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerRouterConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        $loader->load('routing.xml');

        $container->setParameter('router.resource', $config['resource']);
        $container->setParameter('router.cache_class_prefix', $container->getParameter('kernel.name').ucfirst($container->getParameter('kernel.environment')));
        $router = $container->findDefinition('router.default');
        $argument = $router->getArgument(2);
        $argument['strict_requirements'] = $config['strict_requirements'];
        if (isset($config['type'])) {
            $argument['resource_type'] = $config['type'];
        }
        $router->replaceArgument(2, $argument);

        $container->setParameter('request_listener.http_port', $config['http_port']);
        $container->setParameter('request_listener.https_port', $config['https_port']);

        $this->addClassesToCompile(array(
            'Symfony_Component_Routing_Generator_UrlGenerator',
            'Symfony_Component_Routing_RequestContext',
            'Symfony_Component_Routing_Router',
            'Symfony_Bundle_FrameworkBundle_Routing_RedirectableUrlMatcher',
            $container->findDefinition('router.default')->getClass(),
        ));
    }

    /**
     * Loads the session configuration.
     *
     * @param array            $config    A session configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerSessionConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        $loader->load('session.xml');

        // session storage
        $container->setAlias('session.storage', $config['storage_id']);
        $options = array();
        foreach (array('name', 'cookie_lifetime', 'cookie_path', 'cookie_domain', 'cookie_secure', 'cookie_httponly', 'gc_maxlifetime', 'gc_probability', 'gc_divisor') as $key) {
            if (isset($config[$key])) {
                $options[$key] = $config[$key];
            }
        }

        //we deprecated session options without cookie_ prefix, but we are still supporting them,
        //Let's merge the ones that were supplied without prefix
        foreach (array('lifetime', 'path', 'domain', 'secure', 'httponly') as $key) {
            if (!isset($options['cookie_'.$key]) && isset($config[$key])) {
                $options['cookie_'.$key] = $config[$key];
            }
        }
        $container->setParameter('session.storage.options', $options);

        // session handler (the internal callback registered with PHP session management)
        if (null == $config['handler_id']) {
            // Set the handler class to be null
            $container->getDefinition('session.storage.native')->replaceArgument(1, null);
        } else {
            $container->setAlias('session.handler', $config['handler_id']);
        }

        $container->setParameter('session.save_path', $config['save_path']);

        $this->addClassesToCompile(array(
            'Symfony_Bundle_FrameworkBundle_EventListener_SessionListener',
            'Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage',
            'Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeFileSessionHandler',
            'Symfony_Component_HttpFoundation_Session_Storage_Proxy_AbstractProxy',
            'Symfony_Component_HttpFoundation_Session_Storage_Proxy_SessionHandlerProxy',
            $container->getDefinition('session')->getClass(),
        ));

        if ($container->hasDefinition($config['storage_id'])) {
            $this->addClassesToCompile(array(
                $container->findDefinition('session.storage')->getClass(),
            ));
        }
    }

    /**
     * Loads the templating configuration.
     *
     * @param array            $config    A templating configuration array
     * @param string           $ide
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerTemplatingConfiguration(array $config, $ide, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        $loader->load('templating.xml');
        $loader->load('templating_php.xml');

        $links = array(
            'textmate' => 'txmt://open?url=file://%%f&line=%%l',
            'macvim'   => 'mvim://open?url=file://%%f&line=%%l',
        );

        $container->setParameter('templating.helper.code.file_link_format', isset($links[$ide]) ? $links[$ide] : $ide);
        $container->setParameter('templating.helper.form.resources', $config['form']['resources']);
        $container->setParameter('fragment.renderer.hinclude.global_template', $config['hinclude_default_template']);

        if ($container->getParameter('kernel.debug')) {
            $loader->load('templating_debug.xml');
        }

        // create package definitions and add them to the assets helper
        $defaultPackage = $this->createPackageDefinition($container, $config['assets_base_urls']['http'], $config['assets_base_urls']['ssl'], $config['assets_version'], $config['assets_version_format']);
        $container->setDefinition('templating.asset.default_package', $defaultPackage);
        $namedPackages = array();
        foreach ($config['packages'] as $name => $package) {
            $namedPackage = $this->createPackageDefinition($container, $package['base_urls']['http'], $package['base_urls']['ssl'], $package['version'], $package['version_format'], $name);
            $container->setDefinition('templating.asset.package.'.$name, $namedPackage);
            $namedPackages[$name] = new Symfony_Component_DependencyInjection_Reference('templating.asset.package.'.$name);
        }
        $container->getDefinition('templating.helper.assets')->setArguments(array(
            new Symfony_Component_DependencyInjection_Reference('templating.asset.default_package'),
            $namedPackages,
        ));

        // Apply request scope to assets helper if one or more packages are request-scoped
//         $requireRequestScope = array_reduce(
//             $namedPackages,
//             function($v, Symfony_Component_DependencyInjection_Reference $ref) use ($container) {
//                 return $v || 'request' === $container->getDefinition($ref)->getScope();
//             },
//             'request' === $defaultPackage->getScope()
//         );
        // for PHP < 5.3.0 Anonymous functions become available.
        $requireRequestScope = 'request' === $defaultPackage->getScope();
        foreach ($namedPackages as $ref) {
            if (!$ref instanceof Symfony_Component_DependencyInjection_Reference) {
                trigger_error(sprintf('The variable $ref must be an instance of Symfony_Component_DependencyInjection_Reference, %s given, called in %s on line %s',
                    __METHOD__,
                    gettype($ref),
                    __FILE__,
                    __LINE__
                ), E_USER_ERROR);
            }
            if ('request' === $container->getDefinition($ref)->getScope()) {
                $requireRequestScope = true;
                break;
            }
        }

        if ($requireRequestScope) {
            $container->getDefinition('templating.helper.assets')->setScope('request');
        }

        if (!empty($config['loaders'])) {
            $loaders = array_map(create_function('$loader', 'return new Symfony_Component_DependencyInjection_Reference($loader);'), $config['loaders']);

            // Use a delegation unless only a single loader was registered
            if (1 === count($loaders)) {
                $container->setAlias('templating.loader', (string) reset($loaders)->__toString());
            } else {
                $container->getDefinition('templating.loader.chain')->addArgument($loaders);
                $container->setAlias('templating.loader', 'templating.loader.chain');
            }
        }

        $container->setParameter('templating.loader.cache.path', null);
        if (isset($config['cache'])) {
            // Wrap the existing loader with cache (must happen after loaders are registered)
            $container->setDefinition('templating.loader.wrapped', $container->findDefinition('templating.loader'));
            $loaderCache = $container->getDefinition('templating.loader.cache');
            $container->setParameter('templating.loader.cache.path', $config['cache']);

            $container->setDefinition('templating.loader', $loaderCache);
        }

        $this->addClassesToCompile(array(
            'Symfony_Bundle_FrameworkBundle_Templating_GlobalVariables',
            'Symfony_Bundle_FrameworkBundle_Templating_TemplateReference',
            'Symfony_Bundle_FrameworkBundle_Templating_TemplateNameParser',
            $container->findDefinition('templating.locator')->getClass(),
        ));

        if (in_array('php', $config['engines'], true)) {
            $this->addClassesToCompile(array(
                'Symfony_Component_Templating_Storage_FileStorage',
                'Symfony_Bundle_FrameworkBundle_Templating_PhpEngine',
                'Symfony_Bundle_FrameworkBundle_Templating_Loader_FilesystemLoader',
            ));
        }

        $container->setParameter('templating.engines', $config['engines']);
        $engines = array_map(create_function('$engine', 'return new Symfony_Component_DependencyInjection_Reference(\'templating.engine.\'.$engine);'), $config['engines']);

        // Use a delegation unless only a single engine was registered
        if (1 === count($engines)) {
            $container->setAlias('templating', (string) reset($engines)->__toString());
        } else {
            foreach ($engines as $engine) {
                $container->getDefinition('templating.engine.delegating')->addMethodCall('addEngine', array($engine));
            }
            $container->setAlias('templating', 'templating.engine.delegating');
        }
    }

    /**
     * Returns a definition for an asset package.
     */
    private function createPackageDefinition(Symfony_Component_DependencyInjection_ContainerBuilder $container, array $httpUrls, array $sslUrls, $version, $format, $name = null)
    {
        if (!$httpUrls) {
            $package = new Symfony_Component_DependencyInjection_DefinitionDecorator('templating.asset.path_package');
            $package
                ->setPublic(false)
                ->setScope('request')
                ->replaceArgument(1, $version)
                ->replaceArgument(2, $format)
            ;

            return $package;
        }

        if ($httpUrls == $sslUrls) {
            $package = new Symfony_Component_DependencyInjection_DefinitionDecorator('templating.asset.url_package');
            $package
                ->setPublic(false)
                ->replaceArgument(0, $sslUrls)
                ->replaceArgument(1, $version)
                ->replaceArgument(2, $format)
            ;

            return $package;
        }

        $prefix = $name ? 'templating.asset.package.'.$name : 'templating.asset.default_package';

        $httpPackage = new Symfony_Component_DependencyInjection_DefinitionDecorator('templating.asset.url_package');
        $httpPackage
            ->replaceArgument(0, $httpUrls)
            ->replaceArgument(1, $version)
            ->replaceArgument(2, $format)
        ;
        $container->setDefinition($prefix.'.http', $httpPackage);

        if ($sslUrls) {
            $sslPackage = new Symfony_Component_DependencyInjection_DefinitionDecorator('templating.asset.url_package');
            $sslPackage
                ->replaceArgument(0, $sslUrls)
                ->replaceArgument(1, $version)
                ->replaceArgument(2, $format)
            ;
        } else {
            $sslPackage = new Symfony_Component_DependencyInjection_DefinitionDecorator('templating.asset.path_package');
            $sslPackage
                ->setScope('request')
                ->replaceArgument(1, $version)
                ->replaceArgument(2, $format)
            ;
        }
        $container->setDefinition($prefix.'.ssl', $sslPackage);

        $package = new Symfony_Component_DependencyInjection_DefinitionDecorator('templating.asset.request_aware_package');
        $package
            ->setPublic(false)
            ->setScope('request')
            ->replaceArgument(1, $prefix.'.http')
            ->replaceArgument(2, $prefix.'.ssl')
        ;

        return $package;
    }

    /**
     * Loads the translator configuration.
     *
     * @param array            $config    A translator configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     */
    private function registerTranslatorConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        // Use the "real" translator instead of the identity default
        $container->setAlias('translator', 'translator.default');
        $translator = $container->findDefinition('translator.default');
        $translator->addMethodCall('setFallbackLocale', array($config['fallback']));

        // Discover translation directories
        $dirs = array();
        if (class_exists('Symfony_Component_Validator_Validator')) {
            $r = new ReflectionClass('Symfony_Component_Validator_Validator');

            $dirs[] = dirname($r->getFilename()).'/Resources/translations';
        }
        if (class_exists('Symfony_Component_Form_Form')) {
            $r = new ReflectionClass('Symfony_Component_Form_Form');

            $dirs[] = dirname($r->getFilename()).'/Resources/translations';
        }
        if (class_exists('Symfony_Component_Security_Core_Exception_AuthenticationException')) {
            $r = new ReflectionClass('Symfony_Component_Security_Core_Exception_AuthenticationException');

            $dirs[] = dirname($r->getFilename()).'/../../Resources/translations';
        }
        $overridePath = $container->getParameter('kernel.root_dir').'/Resources/%s/translations';
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/translations')) {
                $dirs[] = $dir;
            }
            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $dirs[] = $dir;
            }
        }
        if (is_dir($dir = $container->getParameter('kernel.root_dir').'/Resources/translations')) {
            $dirs[] = $dir;
        }

        // Register translation resources
        if ($dirs) {
            foreach ($dirs as $dir) {
                $container->addResource(new Symfony_Component_Config_Resource_DirectoryResource($dir));
            }
            $finder = Symfony_Component_Finder_Finder::create()
                ->files()
                ->filter(create_function ('SplFileInfo $file', '
                    return 2 === substr_count($file->getBasename(), \'.\') && preg_match(\'/\.\w+$/\', $file->getBasename());
                '))
                ->in($dirs)
            ;

            foreach ($finder as $file) {
                // filename is domain.locale.format
                list($domain, $locale, $format) = explode('.', $file->getBasename(), 3);
                $translator->addMethodCall('addResource', array($format, (string) $file->__toString(), $locale, $domain));
            }
        }
    }

    /**
     * Loads the validator configuration.
     *
     * @param array            $config    A validation configuration array
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_DependencyInjection_Loader_XmlFileLoader    $loader    An XmlFileLoader instance
     */
    private function registerValidationConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_DependencyInjection_Loader_XmlFileLoader $loader)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('validator.xml');

        $container->setParameter('validator.translation_domain', $config['translation_domain']);
        $container->setParameter('validator.mapping.loader.xml_files_loader.mapping_files', $this->getValidatorXmlMappingFiles($container));
        $container->setParameter('validator.mapping.loader.yaml_files_loader.mapping_files', $this->getValidatorYamlMappingFiles($container));

        if (array_key_exists('enable_annotations', $config) && $config['enable_annotations']) {
            $loaderChain = $container->getDefinition('validator.mapping.loader.loader_chain');
            $arguments = $loaderChain->getArguments();
            array_unshift($arguments[0], new Symfony_Component_DependencyInjection_Reference('validator.mapping.loader.annotation_loader'));
            $loaderChain->setArguments($arguments);
        }

        if (isset($config['cache'])) {
            $container->getDefinition('validator.mapping.class_metadata_factory')
                ->replaceArgument(1, new Symfony_Component_DependencyInjection_Reference('validator.mapping.cache.'.$config['cache']));
            $container->setParameter(
                'validator.mapping.cache.prefix',
                'validator_'.md5($container->getParameter('kernel.root_dir'))
            );
        }
    }

    private function getValidatorXmlMappingFiles(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $reflClass = new ReflectionClass('Symfony\Component\Form\FormInterface');
        $files = array(dirname($reflClass->getFileName()).'/Resources/config/validation.xml');
        $container->addResource(new Symfony_Component_Config_Resource_FileResource($files[0]));

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/validation.xml')) {
                $files[] = realpath($file);
                $container->addResource(new Symfony_Component_Config_Resource_FileResource($file));
            }
        }

        return $files;
    }

    private function getValidatorYamlMappingFiles(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $files = array();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/validation.yml')) {
                $files[] = realpath($file);
                $container->addResource(new Symfony_Component_Config_Resource_FileResource($file));
            }
        }

        return $files;
    }

    private function registerAnnotationsConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container,$loader)
    {
        $loader->load('annotations.xml');

        if ('file' === $config['cache']) {
            $cacheDir = $container->getParameterBag()->resolveValue($config['file_cache_dir']);
            if (!is_dir($cacheDir) && false === @mkdir($cacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir));
            }

            $container
                ->getDefinition('annotations.file_cache_reader')
                ->replaceArgument(1, $cacheDir)
                ->replaceArgument(2, $config['debug'])
            ;
            $container->setAlias('annotation_reader', 'annotations.file_cache_reader');
        } elseif ('none' !== $config['cache']) {
            $container
                ->getDefinition('annotations.cached_reader')
                ->replaceArgument(1, new Symfony_Component_DependencyInjection_Reference($config['cache']))
                ->replaceArgument(2, $config['debug'])
            ;
            $container->setAlias('annotation_reader', 'annotations.cached_reader');
        }
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
        return 'http://symfony.com/schema/dic/symfony';
    }
}
