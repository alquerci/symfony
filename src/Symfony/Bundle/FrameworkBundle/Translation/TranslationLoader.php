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
 * TranslationLoader loads translation messages from translation files.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class Symfony_Bundle_FrameworkBundle_Translation_TranslationLoader
{
    /**
     * Loaders used for import.
     *
     * @var array
     */
    private $loaders = array();

    /**
     * Adds a loader to the translation extractor.
     * @param string          $format The format of the loader
     * @param Symfony_Component_Translation_Loader_LoaderInterface $loader
     */
    public function addLoader($format, Symfony_Component_Translation_Loader_LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Loads translation messages from a directory to the catalogue.
     *
     * @param string           $directory the directory to look into
     * @param Symfony_Component_Translation_MessageCatalogue $catalogue the catalogue
     */
    public function loadMessages($directory, Symfony_Component_Translation_MessageCatalogue $catalogue)
    {
        foreach ($this->loaders as $format => $loader) {
            // load any existing translation files
            $finder = new Symfony_Component_Finder_Finder();
            $extension = $catalogue->getLocale().'.'.$format;
            $files = $finder->files()->name('*.'.$extension)->in($directory);
            foreach ($files as $file) {
                $domain = substr($file->getFileName(), 0, -1 * strlen($extension) - 1);
                $catalogue->addCatalogue($loader->load($file->getPathname(), $catalogue->getLocale(), $domain));
            }
        }
    }
}
