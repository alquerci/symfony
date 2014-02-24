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
 * Interface for extensions which provide types, type extensions and a guesser.
 */
interface Symfony_Component_Form_FormExtensionInterface
{
    /**
     * Returns a type by name.
     *
     * @param string $name The name of the type
     *
     * @return Symfony_Component_Form_FormTypeInterface The type
     *
     * @throws Symfony_Component_Form_Exception_FormException if the given type is not supported by this extension
     */
    public function getType($name);

    /**
     * Returns whether the given type is supported.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the type is supported by this extension
     */
    public function hasType($name);

    /**
     * Returns the extensions for the given type.
     *
     * @param string $name The name of the type
     *
     * @return Symfony_Component_Form_FormTypeExtensionInterface[] An array of extensions as Symfony_Component_Form_FormTypeExtensionInterface instances
     */
    public function getTypeExtensions($name);

    /**
     * Returns whether this extension provides type extensions for the given type.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the given type has extensions
     */
    public function hasTypeExtensions($name);

    /**
     * Returns the type guesser provided by this extension.
     *
     * @return Symfony_Component_Form_FormTypeGuesserInterface|null The type guesser
     */
    public function getTypeGuesser();
}
