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
 * Loads multiple yaml mapping files
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * @see    Symfony_Component_Validator_Mapping_Loader_FilesLoader
 */
class Symfony_Component_Validator_Mapping_Loader_YamlFilesLoader extends Symfony_Component_Validator_Mapping_Loader_FilesLoader
{
    /**
     * {@inheritDoc}
     */
    public function getFileLoaderInstance($file)
    {
        return new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader($file);
    }
}
