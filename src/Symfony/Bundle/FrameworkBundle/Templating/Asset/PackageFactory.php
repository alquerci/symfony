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
 * Creates packages based on whether the current request is secure.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Asset_PackageFactory
{
    private $container;

    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns either the HTTP or SSL version of an asset package.
     *
     * @param Symfony_Component_HttpFoundation_Request $request The current request
     * @param string  $httpId  The id for the package to use when the current request is HTTP
     * @param string  $sslId   The id for the package to use when the current request is SSL
     *
     * @return Symfony_Bundle_FrameworkBundle_Templating_Asset_PackageInterface The package
     */
    public function getPackage(Symfony_Component_HttpFoundation_Request $request, $httpId, $sslId)
    {
        return $this->container->get($request->isSecure() ? $sslId : $httpId);
    }
}
