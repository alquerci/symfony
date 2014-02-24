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
 * Creates mapping loaders for array of files.
 *
 * Abstract class, used by
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * @see    Symfony_Component_Validator_Mapping_Loader_YamlFileLoader
 * @see    Symfony_Component_Validator_Mapping_Loader_XmlFileLoader
 */
abstract class Symfony_Component_Validator_Mapping_Loader_FilesLoader extends Symfony_Component_Validator_Mapping_Loader_LoaderChain
{
    /**
     * Array of mapping files.
     *
     * @param array $paths Array of file paths
     */
    public function __construct(array $paths)
    {
        parent::__construct($this->getFileLoaders($paths));
    }

    /**
     * Array of mapping files.
     *
     * @param array $paths Array of file paths
     *
     * @return Symfony_Component_Validator_Mapping_Loader_LoaderInterface[] Array of metadata loaders
     */
    protected function getFileLoaders($paths)
    {
        $loaders = array();
        foreach ($paths as $path) {
            $loaders[] = $this->getFileLoaderInstance($path);
        }

        return $loaders;
    }

    /**
     * Takes mapping file path.
     *
     * @param string $file
     *
     * @return Symfony_Component_Validator_Mapping_Loader_LoaderInterface
     */
    abstract protected function getFileLoaderInstance($file);
}
