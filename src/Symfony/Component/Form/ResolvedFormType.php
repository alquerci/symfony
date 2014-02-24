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
 * A wrapper for a form type and its extensions.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_ResolvedFormType implements Symfony_Component_Form_ResolvedFormTypeInterface
{
    /**
     * @var Symfony_Component_Form_FormTypeInterface
     */
    private $innerType;

    /**
     * @var array
     */
    private $typeExtensions;

    /**
     * @var Symfony_Component_Form_ResolvedFormTypeInterface
     */
    private $parent;

    /**
     * @var Symfony_Component_OptionsResolver_OptionsResolver
     */
    private $optionsResolver;

    public function __construct(Symfony_Component_Form_FormTypeInterface $innerType, array $typeExtensions = array(), Symfony_Component_Form_ResolvedFormTypeInterface $parent = null)
    {
        if (!preg_match('/^[a-z0-9_]*$/i', $innerType->getName())) {
            throw new Symfony_Component_Form_Exception_Exception(sprintf(
                'The "%s" form type name ("%s") is not valid. Names must only contain letters, numbers, and "_".',
                get_class($innerType),
                $innerType->getName()
            ));
        }

        foreach ($typeExtensions as $extension) {
            if (!$extension instanceof Symfony_Component_Form_FormTypeExtensionInterface) {
                throw new Symfony_Component_Form_Exception_UnexpectedTypeException($extension, 'Symfony_Component_Form_FormTypeExtensionInterface');
            }
        }

        // BC
        if ($innerType instanceof Symfony_Component_Form_AbstractType) {
            /* @var Symfony_Component_Form_AbstractType $innerType */
            set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handleBC'));
            $innerType->setExtensions($typeExtensions);
            restore_error_handler();
        }

        $this->innerType = $innerType;
        $this->typeExtensions = $typeExtensions;
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->innerType->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerType()
    {
        return $this->innerType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions()
    {
        // BC
        if ($this->innerType instanceof Symfony_Component_Form_AbstractType) {
            return $this->innerType->getExtensions();
        }

        return $this->typeExtensions;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder(Symfony_Component_Form_FormFactoryInterface $factory, $name, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null)
    {
        $options = $this->getOptionsResolver()->resolve($options);

        // Should be decoupled from the specific option at some point
        $dataClass = isset($options['data_class']) ? $options['data_class'] : null;

        $builder = new Symfony_Component_Form_FormBuilder($name, $dataClass, new Symfony_Component_EventDispatcher_EventDispatcher(), $factory, $options);
        $builder->setType($this);
        $builder->setParent($parent);

        $this->buildForm($builder, $options);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function createView(Symfony_Component_Form_FormInterface $form, Symfony_Component_Form_FormView $parent = null)
    {
        $options = $form->getConfig()->getOptions();

        $view = new Symfony_Component_Form_FormView($parent);

        $this->buildView($view, $form, $options);

        foreach ($form as $name => $child) {
            /* @var Symfony_Component_Form_FormInterface $child */
            $view->children[$name] = $child->createView($view);
        }

        $this->finishView($view, $form, $options);

        return $view;
    }

    /**
     * Configures a form builder for the type hierarchy.
     *
     * This method is protected in order to allow implementing classes
     * to change or call it in re-implementations of {@link createBuilder()}.
     *
     * @param Symfony_Component_Form_FormBuilderInterface $builder The builder to configure.
     * @param array                $options The options used for the configuration.
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildForm($builder, $options);
        }

        $this->innerType->buildForm($builder, $options);

        foreach ($this->typeExtensions as $extension) {
            /* @var Symfony_Component_Form_FormTypeExtensionInterface $extension */
            $extension->buildForm($builder, $options);
        }
    }

    /**
     * Configures a form view for the type hierarchy.
     *
     * This method is protected in order to allow implementing classes
     * to change or call it in re-implementations of {@link createView()}.
     *
     * It is called before the children of the view are built.
     *
     * @param Symfony_Component_Form_FormView      $view    The form view to configure.
     * @param Symfony_Component_Form_FormInterface $form    The form corresponding to the view.
     * @param array         $options The options used for the configuration.
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildView($view, $form, $options);
        }

        $this->innerType->buildView($view, $form, $options);

        foreach ($this->typeExtensions as $extension) {
            /* @var Symfony_Component_Form_FormTypeExtensionInterface $extension */
            $extension->buildView($view, $form, $options);
        }
    }

    /**
     * Finishes a form view for the type hierarchy.
     *
     * This method is protected in order to allow implementing classes
     * to change or call it in re-implementations of {@link createView()}.
     *
     * It is called after the children of the view have been built.
     *
     * @param Symfony_Component_Form_FormView      $view    The form view to configure.
     * @param Symfony_Component_Form_FormInterface $form    The form corresponding to the view.
     * @param array         $options The options used for the configuration.
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->finishView($view, $form, $options);
        }

        $this->innerType->finishView($view, $form, $options);

        foreach ($this->typeExtensions as $extension) {
            /* @var Symfony_Component_Form_FormTypeExtensionInterface $extension */
            $extension->finishView($view, $form, $options);
        }
    }

    /**
     * Returns the configured options resolver used for this type.
     *
     * This method is protected in order to allow implementing classes
     * to change or call it in re-implementations of {@link createBuilder()}.
     *
     * @return Symfony_Component_OptionsResolver_OptionsResolverInterface The options resolver.
     */
    public function getOptionsResolver()
    {
        if (null === $this->optionsResolver) {
            if (null !== $this->parent) {
                $this->optionsResolver = clone $this->parent->getOptionsResolver();
            } else {
                $this->optionsResolver = new Symfony_Component_OptionsResolver_OptionsResolver();
            }

            $this->innerType->setDefaultOptions($this->optionsResolver);

            foreach ($this->typeExtensions as $extension) {
                /* @var Symfony_Component_Form_FormTypeExtensionInterface $extension */
                $extension->setDefaultOptions($this->optionsResolver);
            }
        }

        return $this->optionsResolver;
    }
}
