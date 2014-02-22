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
        // TODO $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_ProfilerPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_RegisterKernelListenersPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TemplatingPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddConstraintValidatorsPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddValidatorInitializersPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_FormPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TranslatorPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheWarmerPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheClearerPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TranslationExtractorPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TranslationDumperPass());
        $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_FragmentRendererPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);

        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_ContainerBuilderDebugDumpPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);
            $container->addCompilerPass(new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_CompilerDebugDumpPass(), Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_AFTER_REMOVING);
        }
    }
}
