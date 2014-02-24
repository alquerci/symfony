<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory implements Symfony_Component_Validator_MetadataFactoryInterface
{
    protected $metadatas = array();

    public function getMetadataFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class)) {
            throw new Symfony_Component_Validator_Exception_NoSuchMetadataException('No metadata for type ' . gettype($class));
        }

        if (!isset($this->metadatas[$class])) {
            throw new Symfony_Component_Validator_Exception_NoSuchMetadataException('No metadata for "' . $class . '"');
        }

        return $this->metadatas[$class];
    }

    public function hasMetadataFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class)) {
            return false;
        }

        return isset($this->metadatas[$class]);
    }

    public function addMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata)
    {
        $this->metadatas[$metadata->getClassName()] = $metadata;
    }
}
