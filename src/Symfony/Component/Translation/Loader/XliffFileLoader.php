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
 * XliffFileLoader loads translations from XLIFF files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_Translation_Loader_XliffFileLoader implements Symfony_Component_Translation_Loader_LoaderInterface
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

        $xml = $this->parseFile($resource);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');

        $catalogue = new Symfony_Component_Translation_MessageCatalogue($locale);
        foreach ($xml->xpath('//xliff:trans-unit') as $translation) {
            $attributes = $translation->attributes();

            if (!(isset($attributes['resname']) || isset($translation->source)) || !isset($translation->target)) {
                continue;
            }

            $source = isset($attributes['resname']) && $attributes['resname'] ? $attributes['resname'] : $translation->source;
            $catalogue->set((string) $source, (string) $translation->target, $domain);
        }
        $catalogue->addResource(new Symfony_Component_Config_Resource_FileResource($resource));

        return $catalogue;
    }

    /**
     * Validates and parses the given file into a SimpleXMLElement
     *
     * @param string $file
     *
     * @throws RuntimeException
     *
     * @return SimpleXMLElement
     *
     * @throws Symfony_Component_Translation_Exception_InvalidResourceException
     */
    private function parseFile($file)
    {
        try {
            $dom = Symfony_Component_Config_Util_XmlUtils::loadFile($file, array($this, 'validateSchema'));
        } catch (InvalidArgumentException $e) {
            throw new Symfony_Component_Translation_Exception_InvalidResourceException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        return simplexml_import_dom($dom);
    }


    /**
     * Validates a documents XML schema.
     *
     * @param DOMDocument $dom
     *
     * @return Boolean
     *
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException When extension references a non-existent XSD file
     */
    public function validateSchema(DOMDocument $dom)
    {
        $location = str_replace('\\', '/', dirname(__FILE__)).'/schema/dic/xliff-core/xml.xsd';
        $parts = explode('/', $location);
        if (0 === stripos($location, 'phar://')) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'sf2');
            if ($tmpfile) {
                // The copy function fails when the source path contains white space
                if (false === strpos($location, ' ')) {
                    copy($location, $tmpfile);
                } else {
                    file_put_contents($tmpfile, file_get_contents($location));
                }
                $parts = explode('/', str_replace('\\', '/', $tmpfile));
            }
        }
        $drive = '\\' === DIRECTORY_SEPARATOR ? array_shift($parts).'/' : '';
        $location = 'file:///'.$drive.implode('/', array_map('rawurlencode', $parts));

        $source = file_get_contents(dirname(__FILE__).'/schema/dic/xliff-core/xliff-core-1.2-strict.xsd');
        $source = str_replace('http://www.w3.org/2001/xml.xsd', $location, $source);

        $valid = @$dom->schemaValidateSource($source);

        return $valid;
    }
}
