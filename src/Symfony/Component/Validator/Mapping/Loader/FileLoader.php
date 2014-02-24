<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Validator_Mapping_Loader_FileLoader extends Symfony_Component_Validator_Mapping_Loader_AbstractLoader
{
    protected $file;

    /**
     * Constructor.
     *
     * @param string $file The mapping file to load
     *
     * @throws Symfony_Component_Validator_Exception_MappingException if the mapping file does not exist
     * @throws Symfony_Component_Validator_Exception_MappingException if the mapping file is not readable
     */
    public function __construct($file)
    {
        if (!is_file($file)) {
            throw new Symfony_Component_Validator_Exception_MappingException(sprintf('The mapping file %s does not exist', $file));
        }

        if (!is_readable($file)) {
            throw new Symfony_Component_Validator_Exception_MappingException(sprintf('The mapping file %s is not readable', $file));
        }

        $this->file = $file;
    }
}
