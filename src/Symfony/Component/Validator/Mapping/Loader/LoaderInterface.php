<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface Symfony_Component_Validator_Mapping_Loader_LoaderInterface
{
    /**
     * Load a Class Metadata.
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadata $metadata A metadata
     *
     * @return Boolean
     */
    public function loadClassMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata);
}
