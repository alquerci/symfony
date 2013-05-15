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
    public function boot()
    {
        if ($trustedProxies = $this->container->getParameter('kernel.trusted_proxies')) {
            Symfony_Component_HttpFoundation_Request::setTrustedProxies($trustedProxies);
        } elseif ($this->container->getParameter('kernel.trust_proxy_headers')) {
            Symfony_Component_HttpFoundation_Request::trustProxyData(); // @deprecated, to be removed in 2.3
        }
    }

    public function build(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        parent::build($container);

        $container->addScope(new Symfony_Component_DependencyInjection_Scope('request'));

        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_RoutingResolverPass());
        // TODO $container->addCompilerPass(new ProfilerPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_RegisterKernelListenersPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);
        // TODO $container->addCompilerPass(new TemplatingPass());
        // TODO $container->addCompilerPass(new AddConstraintValidatorsPass());
        // TODO $container->addCompilerPass(new AddValidatorInitializersPass());
        // TODO $container->addCompilerPass(new FormPass());
        // TODO $container->addCompilerPass(new TranslatorPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheWarmerPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheClearerPass());
        // TODO $container->addCompilerPass(new TranslationExtractorPass());
        // TODO $container->addCompilerPass(new TranslationDumperPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_FragmentRendererPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);

        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_ContainerBuilderDebugDumpPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);
            $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_CompilerDebugDumpPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);
        }
    }
}
