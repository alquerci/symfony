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
 * A factory for {@link ClassMetadata} objects.
 *
 * @deprecated Deprecated since version 2.2, to be removed in 2.3. Implement
 *             {@link Symfony_Component_Validator_MetadataFactoryInterface} instead.
 */
interface Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface
{
    /**
     * Returns metadata for a given class.
     *
     * @param string $class The class name.
     *
     * @return Symfony_Component_Validator_Mapping_ClassMetadata The class metadata instance.
     */
    public function getClassMetadata($class);
}
