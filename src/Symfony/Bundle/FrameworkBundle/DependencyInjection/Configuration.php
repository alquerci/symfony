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
 * FrameworkExtension configuration structure.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_Configuration implements Symfony_Component_Config_Definition_ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return Symfony_Component_Config_Definition_Builder_TreeBuilder The tree builder
     *
     * @throws RuntimeException When using the deprecated 'charset' setting
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $rootNode = $treeBuilder->root('framework');

        $rootNode
            ->children()
                ->scalarNode('charset')
                    ->defaultNull()
                    ->beforeNormalization()
                        ->ifTrue(create_function('$v', 'return null !== $v;'))
                        ->then(create_function('$v', '
                            $message = \'The charset setting is deprecated. Just remove it from your configuration file.\';

                            if (\'UTF-8\' !== $v) {
                                $message .= sprintf(\'You need to define a getCharset() method in your Application Kernel class that returns "%s".\', $v);
                            }

                            throw new RuntimeException($message);
                        '))
                    ->end()
                ->end()
                ->scalarNode('secret')->end()
                ->scalarNode('trust_proxy_headers')->defaultFalse()->end() // @deprecated, to be removed in 2.3
                ->arrayNode('trusted_proxies')
                    ->beforeNormalization()
                        ->ifTrue(create_function('$v', 'return !is_array($v) && !is_null($v);'))
                        ->then(create_function('$v', 'return is_bool($v) ? array() : preg_split(\'/\s*,\s*/\', $v);'))
                    ->end()
                    ->prototype('scalar')
                        ->validate()
                            ->ifTrue(create_function('$v', 'return !empty($v) && !filter_var($v, FILTER_VALIDATE_IP);'))
                            ->thenInvalid('Invalid proxy IP "%s"')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('ide')->defaultNull()->end()
                ->booleanNode('test')->end()
                ->scalarNode('default_locale')->defaultValue('en')->end()
            ->end()
        ;

        $this->addFormSection($rootNode);
        $this->addEsiSection($rootNode);
        $this->addFragmentsSection($rootNode);
        $this->addProfilerSection($rootNode);
        $this->addRouterSection($rootNode);
        $this->addSessionSection($rootNode);
        $this->addTemplatingSection($rootNode);
        $this->addTranslatorSection($rootNode);
        $this->addValidationSection($rootNode);
        $this->addAnnotationsSection($rootNode);

        return $treeBuilder;
    }

    private function addFormSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('form')
                    ->info('form configuration')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('csrf_protection')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('field_name')->defaultValue('_token')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addEsiSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('esi')
                    ->info('esi configuration')
                    ->canBeEnabled()
                ->end()
            ->end()
        ;
    }

    private function addFragmentsSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('fragments')
                    ->info('fragments configuration')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('path')->defaultValue('/_fragment')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addProfilerSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('profiler')
                    ->info('profiler configuration')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('only_exceptions')->defaultFalse()->end()
                        ->booleanNode('only_master_requests')->defaultFalse()->end()
                        ->scalarNode('dsn')->defaultValue('file:%kernel.cache_dir%/profiler')->end()
                        ->scalarNode('username')->defaultValue('')->end()
                        ->scalarNode('password')->defaultValue('')->end()
                        ->scalarNode('lifetime')->defaultValue(86400)->end()
                        ->arrayNode('matcher')
                            ->canBeUnset()
                            ->performNoDeepMerging()
                            ->children()
                                ->scalarNode('ip')->end()
                                ->scalarNode('path')
                                    ->info('use the urldecoded format')
                                    ->example('^/path to resource/')
                                ->end()
                                ->scalarNode('service')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addRouterSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('router')
                    ->info('router configuration')
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('resource')->isRequired()->end()
                        ->scalarNode('type')->end()
                        ->scalarNode('http_port')->defaultValue(80)->end()
                        ->scalarNode('https_port')->defaultValue(443)->end()
                        ->scalarNode('strict_requirements')
                            ->info(
                                "set to true to throw an exception when a parameter does not match the requirements\n".
                                "set to false to disable exceptions when a parameter does not match the requirements (and return null instead)\n".
                                "set to null to disable parameter checks against requirements\n".
                                "'true' is the preferred configuration in development mode, while 'false' or 'null' might be preferred in production"
                            )
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addSessionSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('session')
                    ->info('session configuration')
                    ->canBeUnset()
                    ->children()
                        ->booleanNode('auto_start')
                            ->info('DEPRECATED! Session starts on demand')
                            ->defaultFalse()
                            ->beforeNormalization()
                                ->ifTrue(create_function('$v', 'return null !== $v;'))
                                ->then(create_function('$v', '
                                    throw new RuntimeException(\'The auto_start setting is deprecated. Just remove it from your configuration file.\');
                                '))
                            ->end()
                        ->end()
                        ->scalarNode('storage_id')->defaultValue('session.storage.native')->end()
                        ->scalarNode('handler_id')->defaultValue('session.handler.native_file')->end()
                        ->scalarNode('name')->end()
                        ->scalarNode('cookie_lifetime')->end()
                        ->scalarNode('cookie_path')->end()
                        ->scalarNode('cookie_domain')->end()
                        ->booleanNode('cookie_secure')->end()
                        ->booleanNode('cookie_httponly')->end()
                        ->scalarNode('gc_divisor')->end()
                        ->scalarNode('gc_probability')->end()
                        ->scalarNode('gc_maxlifetime')->end()
                        ->scalarNode('save_path')->defaultValue('%kernel.cache_dir%/sessions')->end()
                        ->scalarNode('lifetime')->info('DEPRECATED! Please use: cookie_lifetime')->end()
                        ->scalarNode('path')->info('DEPRECATED! Please use: cookie_path')->end()
                        ->scalarNode('domain')->info('DEPRECATED! Please use: cookie_domain')->end()
                        ->booleanNode('secure')->info('DEPRECATED! Please use: cookie_secure')->end()
                        ->booleanNode('httponly')->info('DEPRECATED! Please use: cookie_httponly')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addTemplatingSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $organizeUrls = create_function('$urls', '
            $urls += array(
                \'http\' => array(),
                \'ssl\'  => array(),
            );

            foreach ($urls as $i => $url) {
                if (is_integer($i)) {
                    if (0 === strpos($url, \'https://\') || 0 === strpos($url, \'//\')) {
                        $urls[\'http\'][] = $urls[\'ssl\'][] = $url;
                    } else {
                        $urls[\'http\'][] = $url;
                    }
                    unset($urls[$i]);
                }
            }

            return $urls;
        ');

        $rootNode
            ->children()
                ->arrayNode('templating')
                    ->info('templating configuration')
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('assets_version')->defaultValue(null)->end()
                        ->scalarNode('assets_version_format')->defaultValue('%%s?%%s')->end()
                        ->scalarNode('hinclude_default_template')->defaultNull()->end()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->fixXmlConfig('resource')
                            ->children()
                                ->arrayNode('resources')
                                    ->addDefaultChildrenIfNoneSet()
                                    ->prototype('scalar')->defaultValue('FrameworkBundle:Form')->end()
                                    ->validate()
                                        ->ifTrue(create_function('$v', 'return !in_array(\'FrameworkBundle:Form\', $v);'))
                                        ->then(create_function('$v', '
                                            return array_merge(array(\'FrameworkBundle:Form\'), $v);
                                        '))
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->fixXmlConfig('assets_base_url')
                    ->children()
                        ->arrayNode('assets_base_urls')
                            ->performNoDeepMerging()
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifTrue(create_function('$v', 'return !is_array($v);'))
                                ->then(create_function('$v', 'return array($v);'))
                            ->end()
                            ->beforeNormalization()
                                ->always()
                                ->then($organizeUrls)
                            ->end()
                            ->children()
                                ->arrayNode('http')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('ssl')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('cache')->end()
                    ->end()
                    ->fixXmlConfig('engine')
                    ->children()
                        ->arrayNode('engines')
                            ->example(array('twig'))
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->beforeNormalization()
                                ->ifTrue(create_function('$v', 'return !is_array($v);'))
                                ->then(create_function('$v', 'return array($v); '))
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                    ->fixXmlConfig('loader')
                    ->children()
                        ->arrayNode('loaders')
                            ->beforeNormalization()
                                ->ifTrue(create_function('$v', 'return !is_array($v);'))
                                ->then(create_function('$v' , 'return array($v);'))
                             ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                    ->fixXmlConfig('package')
                    ->children()
                        ->arrayNode('packages')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->fixXmlConfig('base_url')
                                ->children()
                                    ->scalarNode('version')->defaultNull()->end()
                                    ->scalarNode('version_format')->defaultValue('%%s?%%s')->end()
                                    ->arrayNode('base_urls')
                                        ->performNoDeepMerging()
                                        ->addDefaultsIfNotSet()
                                        ->beforeNormalization()
                                            ->ifTrue(create_function('$v', 'return !is_array($v);'))
                                            ->then(create_function('$v', 'return array($v);'))
                                        ->end()
                                        ->beforeNormalization()
                                            ->always()
                                            ->then($organizeUrls)
                                        ->end()
                                        ->children()
                                            ->arrayNode('http')
                                                ->prototype('scalar')->end()
                                            ->end()
                                            ->arrayNode('ssl')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addTranslatorSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('translator')
                    ->info('translator configuration')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('fallback')->defaultValue('en')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addValidationSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('validation')
                    ->info('validation configuration')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('cache')->end()
                        ->booleanNode('enable_annotations')->defaultFalse()->end()
                        ->scalarNode('translation_domain')->defaultValue('validators')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addAnnotationsSection(Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('annotations')
                    ->info('annotation configuration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('cache')->defaultValue('file')->end()
                        ->scalarNode('file_cache_dir')->defaultValue('%kernel.cache_dir%/annotations')->end()
                        ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
