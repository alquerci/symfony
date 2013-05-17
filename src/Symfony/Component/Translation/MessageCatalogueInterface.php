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
 * MessageCatalogueInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_Translation_MessageCatalogueInterface
{
    /**
     * Gets the catalogue locale.
     *
     * @return string The locale
     *
     * @api
     */
    public function getLocale();

    /**
     * Gets the domains.
     *
     * @return array An array of domains
     *
     * @api
     */
    public function getDomains();

    /**
     * Gets the messages within a given domain.
     *
     * If $domain is null, it returns all messages.
     *
     * @param string $domain The domain name
     *
     * @return array An array of messages
     *
     * @api
     */
    public function all($domain = null);

    /**
     * Sets a message translation.
     *
     * @param string $id          The message id
     * @param string $translation The messages translation
     * @param string $domain      The domain name
     *
     * @api
     */
    public function set($id, $translation, $domain = 'messages');

    /**
     * Checks if a message has a translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return Boolean true if the message has a translation, false otherwise
     *
     * @api
     */
    public function has($id, $domain = 'messages');

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return Boolean true if the message has a translation, false otherwise
     *
     * @api
     */
    public function defines($id, $domain = 'messages');

    /**
     * Gets a message translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return string The message translation
     *
     * @api
     */
    public function get($id, $domain = 'messages');

    /**
     * Sets translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @api
     */
    public function replace($messages, $domain = 'messages');

    /**
     * Adds translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @api
     */
    public function add($messages, $domain = 'messages');

    /**
     * Merges translations from the given Catalogue into the current one.
     *
     * The two catalogues must have the same locale.
     *
     * @param Symfony_Component_Translation_MessageCatalogueInterface $catalogue A MessageCatalogueInterface instance
     *
     * @api
     */
    public function addCatalogue(Symfony_Component_Translation_MessageCatalogueInterface $catalogue);

    /**
     * Merges translations from the given Catalogue into the current one
     * only when the translation does not exist.
     *
     * This is used to provide default translations when they do not exist for the current locale.
     *
     * @param Symfony_Component_Translation_MessageCatalogueInterface $catalogue A MessageCatalogueInterface instance
     *
     * @api
     */
    public function addFallbackCatalogue(Symfony_Component_Translation_MessageCatalogueInterface $catalogue);

    /**
     * Gets the fallback catalogue.
     *
     * @return Symfony_Component_Translation_MessageCatalogueInterface|null A MessageCatalogueInterface instance or null when no fallback has been set
     *
     * @api
     */
    public function getFallbackCatalogue();

    /**
     * Returns an array of resources loaded to build this collection.
     *
     * @return Symfony_Component_Config_Resource_ResourceInterface[] An array of resources
     *
     * @api
     */
    public function getResources();

    /**
     * Adds a resource for this collection.
     *
     * @param Symfony_Component_Config_Resource_ResourceInterface $resource A resource instance
     *
     * @api
     */
    public function addResource(Symfony_Component_Config_Resource_ResourceInterface $resource);
}
