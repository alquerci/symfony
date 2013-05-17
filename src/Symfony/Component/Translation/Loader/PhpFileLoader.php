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
 * PhpFileLoader loads translations from PHP files returning an array of translations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_Translation_Loader_PhpFileLoader extends Symfony_Component_Translation_Loader_ArrayLoader implements Symfony_Component_Translation_Loader_LoaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new Symfony_Component_Translation_Exception_InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }

        if (!file_exists($resource)) {
            throw new Symfony_Component_Translation_Exception_NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }

        $messages = require($resource);

        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new Symfony_Component_Config_Resource_FileResource($resource));

        return $catalogue;
    }
}
