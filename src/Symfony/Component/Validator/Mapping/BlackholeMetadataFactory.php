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
 * Simple implementation of ClassMetadataFactoryInterface that can be used when using ValidatorInterface::validateValue().
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Validator_Mapping_BlackholeMetadataFactory implements Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface
{
    public function getClassMetadata($class)
    {
        throw new LogicException('BlackholeClassMetadataFactory only works with ValidatorInterface::validateValue().');
    }
}
