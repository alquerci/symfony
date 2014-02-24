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
 * Creates ResolvedFormTypeInterface instances.
 *
 * This interface allows you to use your custom ResolvedFormTypeInterface
 * implementation, within which you can customize the concrete FormBuilderInterface
 * implementations or FormView subclasses that are used by the framework.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Form_ResolvedFormTypeFactoryInterface
{
    /**
     * Resolves a form type.
     *
     * @param Symfony_Component_Form_FormTypeInterface         $type
     * @param array                     $typeExtensions
     * @param Symfony_Component_Form_ResolvedFormTypeInterface $parent
     *
     * @return Symfony_Component_Form_ResolvedFormTypeInterface
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the types parent {@link FormTypeInterface::getParent()} is not a string
     * @throws Symfony_Component_Form_Exception_FormException           if the types parent can not be retrieved from any extension
     */
    public function createResolvedType(Symfony_Component_Form_FormTypeInterface $type, array $typeExtensions, Symfony_Component_Form_ResolvedFormTypeInterface $parent = null);
}
