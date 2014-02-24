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
class Symfony_Component_Form_FormRegistry implements Symfony_Component_Form_FormRegistryInterface
{
    /**
     * Extensions
     *
     * @var Symfony_Component_Form_FormExtensionInterface[] An array of Symfony_Component_Form_FormExtensionInterface
     */
    private $extensions = array();

    /**
     * @var array
     */
    private $types = array();

    /**
     * @var Symfony_Component_Form_FormTypeGuesserInterface|false|null
     */
    private $guesser = false;

    /**
     * @var Symfony_Component_Form_ResolvedFormTypeFactoryInterface
     */
    private $resolvedTypeFactory;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Form_FormExtensionInterface[]         $extensions          An array of FormExtensionInterface
     * @param Symfony_Component_Form_ResolvedFormTypeFactoryInterface $resolvedTypeFactory The factory for resolved form types.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if any extension does not implement FormExtensionInterface
     */
    public function __construct(array $extensions, Symfony_Component_Form_ResolvedFormTypeFactoryInterface $resolvedTypeFactory)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof Symfony_Component_Form_FormExtensionInterface) {
                throw new Symfony_Component_Form_Exception_UnexpectedTypeException($extension, 'Symfony_Component_Form_FormExtensionInterface');
            }
        }

        $this->extensions = $extensions;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function addType(Symfony_Component_Form_ResolvedFormTypeInterface $type)
    {
        trigger_error('addType() is deprecated since version 2.1 and will be removed in 2.3. Use form extensions or type registration in the Dependency Injection Container instead.', E_USER_DEPRECATED);

        $this->types[$type->getName()] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!is_string($name)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($name, 'string');
        }

        if (!isset($this->types[$name])) {
            /** @var Symfony_Component_Form_FormTypeInterface $type */
            $type = null;

            foreach ($this->extensions as $extension) {
                /* @var Symfony_Component_Form_FormExtensionInterface $extension */
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);
                    break;
                }
            }

            if (!$type) {
                throw new Symfony_Component_Form_Exception_Exception(sprintf('Could not load type "%s"', $name));
            }

            $this->resolveAndAddType($type);
        }

        return $this->types[$name];
    }

    /**
     * Wraps a type into a ResolvedFormTypeInterface implementation and connects
     * it with its parent type.
     *
     * @param Symfony_Component_Form_FormTypeInterface $type The type to resolve.
     *
     * @return Symfony_Component_Form_ResolvedFormTypeInterface The resolved type.
     */
    private function resolveAndAddType(Symfony_Component_Form_FormTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof Symfony_Component_Form_FormTypeInterface) {
            $this->resolveAndAddType($parentType);
            $parentType = $parentType->getName();
        }

        $typeExtensions = array();

        foreach ($this->extensions as $extension) {
            /* @var Symfony_Component_Form_FormExtensionInterface $extension */
            $typeExtensions = array_merge(
                $typeExtensions,
                $extension->getTypeExtensions($type->getName())
            );
        }

        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handleBC'));
        $this->addType($this->resolvedTypeFactory->createResolvedType(
            $type,
            $typeExtensions,
            $parentType ? $this->getType($parentType) : null
        ));
        restore_error_handler();
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (Symfony_Component_Form_Exception_ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (false === $this->guesser) {
            $guessers = array();

            foreach ($this->extensions as $extension) {
                /* @var Symfony_Component_Form_FormExtensionInterface $extension */
                $guesser = $extension->getTypeGuesser();

                if ($guesser) {
                    $guessers[] = $guesser;
                }
            }

            $this->guesser = !empty($guessers) ? new Symfony_Component_Form_FormTypeGuesserChain($guessers) : null;
        }

        return $this->guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
