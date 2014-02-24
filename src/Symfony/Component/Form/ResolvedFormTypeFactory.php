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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_ResolvedFormTypeFactory implements Symfony_Component_Form_ResolvedFormTypeFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResolvedType(Symfony_Component_Form_FormTypeInterface $type, array $typeExtensions, Symfony_Component_Form_ResolvedFormTypeInterface $parent = null)
    {
        return new Symfony_Component_Form_ResolvedFormType($type, $typeExtensions, $parent);
    }
}
