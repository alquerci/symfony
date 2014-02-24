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
 * Returns {@link MetadataInterface} instances for values.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Validator_MetadataFactoryInterface
{
    /**
     * Returns the metadata for the given value.
     *
     * @param mixed $value Some value.
     *
     * @return Symfony_Component_Validator_MetadataInterface The metadata for the value.
     *
     * @throws Symfony_Component_Validator_Exception_NoSuchMetadataException If no metadata exists for the value.
     */
    public function getMetadataFor($value);

    /**
     * Returns whether metadata exists for the given value.
     *
     * @param mixed $value Some value.
     *
     * @return Boolean Whether metadata exists for the value.
     */
    public function hasMetadataFor($value);
}
