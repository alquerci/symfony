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
 * YamlFileLoader loads translations from Yaml files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_Translation_Loader_YamlFileLoader extends Symfony_Component_Translation_Loader_ArrayLoader implements Symfony_Component_Translation_Loader_LoaderInterface
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

        try {
            $messages = Symfony_Component_Yaml_Yaml::parse($resource);
        } catch (Symfony_Component_Yaml_Exception_ParseException $e) {
            throw new Symfony_Component_Translation_Exception_InvalidResourceException('Error parsing YAML.', 0, $e);
        }

        // empty file
        if (null === $messages) {
            $messages = array();
        }

        // not an array
        if (!is_array($messages)) {
            throw new Symfony_Component_Translation_Exception_InvalidResourceException(sprintf('The file "%s" must contain a YAML array.', $resource));
        }

        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new Symfony_Component_Config_Resource_FileResource($resource));

        return $catalogue;
    }
}
