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
 * Persists Symfony_Component_Validator_Mapping_ClassMetadata instances in a cache
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Validator_Mapping_Cache_CacheInterface
{
    /**
     * Returns whether metadata for the given class exists in the cache
     *
     * @param string $class
     */
    public function has($class);

    /**
     * Returns the metadata for the given class from the cache
     *
     * @param string $class Class Name
     *
     * @return Symfony_Component_Validator_Mapping_ClassMetadata|false A Symfony_Component_Validator_Mapping_ClassMetadata instance or false on miss
     */
    public function read($class);

    /**
     * Stores a class metadata in the cache
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadata $metadata A Class Metadata
     */
    public function write(Symfony_Component_Validator_Mapping_ClassMetadata $metadata);
}
