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
 * A builder for FormFactoryInterface objects.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Form_FormFactoryBuilderInterface
{
    /**
     * Sets the factory for creating ResolvedFormTypeInterface instances.
     *
     * @param Symfony_Component_Form_ResolvedFormTypeFactoryInterface $resolvedTypeFactory
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function setResolvedTypeFactory(Symfony_Component_Form_ResolvedFormTypeFactoryInterface $resolvedTypeFactory);

    /**
     * Adds an extension to be loaded by the factory.
     *
     * @param Symfony_Component_Form_FormExtensionInterface $extension The extension.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addExtension(Symfony_Component_Form_FormExtensionInterface $extension);

    /**
     * Adds a list of extensions to be loaded by the factory.
     *
     * @param array $extensions The extensions.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addExtensions(array $extensions);

    /**
     * Adds a form type to the factory.
     *
     * @param Symfony_Component_Form_FormTypeInterface $type The form type.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addType(Symfony_Component_Form_FormTypeInterface $type);

    /**
     * Adds a list of form types to the factory.
     *
     * @param array $types The form types.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addTypes(array $types);

    /**
     * Adds a form type extension to the factory.
     *
     * @param Symfony_Component_Form_FormTypeExtensionInterface $typeExtension The form type extension.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addTypeExtension(Symfony_Component_Form_FormTypeExtensionInterface $typeExtension);

    /**
     * Adds a list of form type extensions to the factory.
     *
     * @param array $typeExtensions The form type extensions.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addTypeExtensions(array $typeExtensions);

    /**
     * Adds a type guesser to the factory.
     *
     * @param Symfony_Component_Form_FormTypeGuesserInterface $typeGuesser The type guesser.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addTypeGuesser(Symfony_Component_Form_FormTypeGuesserInterface $typeGuesser);

    /**
     * Adds a list of type guessers to the factory.
     *
     * @param array $typeGuessers The type guessers.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The builder.
     */
    public function addTypeGuessers(array $typeGuessers);

    /**
     * Builds and returns the factory.
     *
     * @return Symfony_Component_Form_FormFactoryInterface The form factory.
     */
    public function getFormFactory();
}
