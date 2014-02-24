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
abstract class Symfony_Component_Form_AbstractExtension implements Symfony_Component_Form_FormExtensionInterface
{
    /**
     * The types provided by this extension
     * @var Symfony_Component_Form_FormTypeInterface[] An array of Symfony_Component_Form_FormTypeInterface
     */
    private $types;

    /**
     * The type extensions provided by this extension
     * @var Symfony_Component_Form_FormTypeExtensionInterface[] An array of Symfony_Component_Form_FormTypeExtensionInterface
     */
    private $typeExtensions;

    /**
     * The type guesser provided by this extension
     * @var Symfony_Component_Form_FormTypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * Whether the type guesser has been loaded
     * @var Boolean
     */
    private $typeGuesserLoaded = false;

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (null === $this->types) {
            $this->initTypes();
        }

        if (!isset($this->types[$name])) {
            throw new Symfony_Component_Form_Exception_Exception(sprintf('The type "%s" can not be loaded by this extension', $name));
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (null === $this->types) {
            $this->initTypes();
        }

        return isset($this->types[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        if (null === $this->typeExtensions) {
            $this->initTypeExtensions();
        }

        return isset($this->typeExtensions[$name])
            ? $this->typeExtensions[$name]
            : array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        if (null === $this->typeExtensions) {
            $this->initTypeExtensions();
        }

        return isset($this->typeExtensions[$name]) && count($this->typeExtensions[$name]) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (!$this->typeGuesserLoaded) {
            $this->initTypeGuesser();
        }

        return $this->typeGuesser;
    }

    /**
     * Registers the types.
     *
     * @return Symfony_Component_Form_FormTypeInterface[] An array of Symfony_Component_Form_FormTypeInterface instances
     */
    protected function loadTypes()
    {
        return array();
    }

    /**
     * Registers the type extensions.
     *
     * @return Symfony_Component_Form_FormTypeExtensionInterface[] An array of Symfony_Component_Form_FormTypeExtensionInterface instances
     */
    protected function loadTypeExtensions()
    {
        return array();
    }

    /**
     * Registers the type guesser.
     *
     * @return Symfony_Component_Form_FormTypeGuesserInterface|null A type guesser
     */
    protected function loadTypeGuesser()
    {
        return null;
    }

    /**
     * Initializes the types.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if any registered type is not an instance of FormTypeInterface
     */
    private function initTypes()
    {
        $this->types = array();

        foreach ($this->loadTypes() as $type) {
            if (!$type instanceof Symfony_Component_Form_FormTypeInterface) {
                throw new Symfony_Component_Form_Exception_UnexpectedTypeException($type, 'Symfony_Component_Form_FormTypeInterface');
            }

            $this->types[$type->getName()] = $type;
        }
    }

    /**
     * Initializes the type extensions.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if any registered type extension is not
     *                                 an instance of Symfony_Component_Form_FormTypeExtensionInterface
     */
    private function initTypeExtensions()
    {
        $this->typeExtensions = array();

        foreach ($this->loadTypeExtensions() as $extension) {
            if (!$extension instanceof Symfony_Component_Form_FormTypeExtensionInterface) {
                throw new Symfony_Component_Form_Exception_UnexpectedTypeException($extension, 'Symfony_Component_Form_FormTypeExtensionInterface');
            }

            $type = $extension->getExtendedType();

            $this->typeExtensions[$type][] = $extension;
        }
    }

    /**
     * Initializes the type guesser.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the type guesser is not an instance of Symfony_Component_Form_FormTypeGuesserInterface
     */
    private function initTypeGuesser()
    {
        $this->typeGuesserLoaded = true;

        $this->typeGuesser = $this->loadTypeGuesser();
        if (null !== $this->typeGuesser && !$this->typeGuesser instanceof Symfony_Component_Form_FormTypeGuesserInterface) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($this->typeGuesser, 'Symfony_Component_Form_FormTypeGuesserInterface');
        }
    }
}
