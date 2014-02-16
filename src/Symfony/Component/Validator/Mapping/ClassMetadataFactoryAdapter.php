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
 * An adapter for exposing {@link ClassMetadataFactoryInterface} implementations
 * under the new {@link Symfony_Component_Validator_MetadataFactoryInterface}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_Mapping_ClassMetadataFactoryAdapter implements Symfony_Component_Validator_MetadataFactoryInterface
{
    /**
     * @var Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface
     */
    private $innerFactory;

    public function __construct(Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface $innerFactory)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error(sprintf('ClassMetadataFactoryInterface is deprecated since version 2.1 and will be removed in 2.3. Implement MetadataFactoryInterface instead on %s.', get_class($innerFactory)), E_USER_DEPRECATED);

        $this->innerFactory = $innerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        $class = is_object($value) ? get_class($value) : $value;
        $metadata = $this->innerFactory->getClassMetadata($class);

        if (null === $metadata) {
            throw new Symfony_Component_Validator_Exception_NoSuchMetadataException('No metadata exists for class '. $class);
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        $class = is_object($value) ? get_class($value) : $value;

        $return = null !== $this->innerFactory->getClassMetadata($class);

        return $return;
    }
}
