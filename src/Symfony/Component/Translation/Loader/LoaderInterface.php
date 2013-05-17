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
 * LoaderInterface is the interface implemented by all translation loaders.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_Translation_Loader_LoaderInterface
{
    /**
     * Loads a locale.
     *
     * @param mixed  $resource A resource
     * @param string $locale   A locale
     * @param string $domain   The domain
     *
     * @return Symfony_Component_Translation_MessageCatalogue A MessageCatalogue instance
     *
     * @api
     *
     * @throws Symfony_Component_Translation_Exception_NotFoundResourceException when the resource cannot be found
     * @throws Symfony_Component_Translation_Exception_InvalidResourceException  when the resource cannot be loaded
     */
    public function load($resource, $locale, $domain = 'messages');
}
