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
 * A factory for creating metadata for PHP classes.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_Mapping_ClassMetadataFactory implements Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface, Symfony_Component_Validator_MetadataFactoryInterface
{
    /**
     * The loader for loading the class metadata
     * @var Symfony_Component_Validator_Mapping_Loader_LoaderInterface
     */
    protected $loader;

    /**
     * The cache for caching class metadata
     * @var Symfony_Component_Validator_Mapping_Cache_CacheInterface
     */
    protected $cache;

    protected $loadedClasses = array();

    public function __construct(Symfony_Component_Validator_Mapping_Loader_LoaderInterface $loader = null, Symfony_Component_Validator_Mapping_Cache_CacheInterface $cache = null)
    {
        $this->loader = $loader;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        if (!is_object($value) && !is_string($value)) {
            throw new Symfony_Component_Validator_Exception_NoSuchMetadataException('Cannot create metadata for non-objects. Got: ' . gettype($value));
        }

        $class = ltrim(is_object($value) ? get_class($value) : $value, '\\');

        if (isset($this->loadedClasses[$class])) {
            return $this->loadedClasses[$class];
        }

        if (null !== $this->cache && false !== ($this->loadedClasses[$class] = $this->cache->read($class))) {
            return $this->loadedClasses[$class];
        }

        if (!class_exists($class) && !interface_exists($class)) {
            throw new Symfony_Component_Validator_Exception_NoSuchMetadataException('The class or interface "' . $class . '" does not exist.');
        }

        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata($class);

        // Include constraints from the parent class
        if ($parent = $metadata->getReflectionClass()->getParentClass()) {
            $metadata->mergeConstraints($this->getMetadataFor($parent->name));
        }

        // Include constraints from all implemented interfaces
        foreach ($metadata->getReflectionClass()->getInterfaces() as $interface) {
            if ('Symfony_Component_Validator_GroupSequenceProviderInterface' === $interface->name) {
                continue;
            }
            $metadata->mergeConstraints($this->getMetadataFor($interface->name));
        }

        if (null !== $this->loader) {
            $this->loader->loadClassMetadata($metadata);
        }

        if (null !== $this->cache) {
            $this->cache->write($metadata);
        }

        return $this->loadedClasses[$class] = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        if (!is_object($value) && !is_string($value)) {
            return false;
        }

        $class = ltrim(is_object($value) ? get_class($value) : $value, '\\');

        if (class_exists($class) || interface_exists($class)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3. Use
     *             {@link getMetadataFor} instead.
     */
    public function getClassMetadata($class)
    {
        trigger_error('getClassMetadata() is deprecated since version 2.2 and will be removed in 2.3. Use getMetadataFor() instead.', E_USER_DEPRECATED);

        return $this->getMetadataFor($class);
    }
}
