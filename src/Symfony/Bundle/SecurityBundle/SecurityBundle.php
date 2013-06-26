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
class Symfony_Bundle_SecurityBundle_SecurityBundle extends Symfony_Component_HttpKernel_Bundle_Bundle
{
    public function build(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_FormLoginFactory());
        $extension->addSecurityListenerFactory(new Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_HttpBasicFactory());
        $extension->addSecurityListenerFactory(new Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_HttpDigestFactory());
        $extension->addSecurityListenerFactory(new Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_RememberMeFactory());
        $extension->addSecurityListenerFactory(new Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_X509Factory());

        $extension->addUserProviderFactory(new Symfony_Bundle_SecurityBundle_DependencyInjection_Security_UserProvider_InMemoryFactory());
        $container->addCompilerPass(new Symfony_Bundle_SecurityBundle_DependencyInjection_Compiler_AddSecurityVotersPass());
    }
}
