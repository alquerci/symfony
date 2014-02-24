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
 * The central registry of the Form component.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Form_FormRegistryInterface
{
    /**
     * Adds a form type.
     *
     * @param Symfony_Component_Form_ResolvedFormTypeInterface $type The type
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             form extensions or type registration in the Dependency
     *             Injection Container instead.
     */
    public function addType(Symfony_Component_Form_ResolvedFormTypeInterface $type);

    /**
     * Returns a form type by name.
     *
     * This methods registers the type extensions from the form extensions.
     *
     * @param string $name The name of the type
     *
     * @return Symfony_Component_Form_ResolvedFormTypeInterface The type
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the passed name is not a string
     * @throws Symfony_Component_Form_Exception_FormException           if the type can not be retrieved from any extension
     */
    public function getType($name);

    /**
     * Returns whether the given form type is supported.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the type is supported
     */
    public function hasType($name);

    /**
     * Returns the guesser responsible for guessing types.
     *
     * @return Symfony_Component_Form_FormTypeGuesserInterface|null
     */
    public function getTypeGuesser();

    /**
     * Returns the extensions loaded by the framework.
     *
     * @return array
     */
    public function getExtensions();
}
