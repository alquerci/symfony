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
 * A builder for creating {@link Form} instances.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_FormBuilder extends Symfony_Component_Form_FormConfigBuilder implements IteratorAggregate, Symfony_Component_Form_FormBuilderInterface
{
    /**
     * The children of the form builder.
     *
     * @var array
     */
    private $children = array();

    /**
     * The data of children who haven't been converted to form builders yet.
     *
     * @var array
     */
    private $unresolvedChildren = array();

    /**
     * The parent of this builder.
     *
     * @var Symfony_Component_Form_FormBuilder
     */
    private $parent;

    /**
     * Creates a new form builder.
     *
     * @param string                   $name
     * @param string                   $dataClass
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher
     * @param Symfony_Component_Form_FormFactoryInterface     $factory
     * @param array                    $options
     */
    public function __construct($name, $dataClass, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher, Symfony_Component_Form_FormFactoryInterface $factory, array $options = array())
    {
        parent::__construct($name, $dataClass, $dispatcher, $options);

        $this->setFormFactory($factory);
    }

    /**
     * {@inheritdoc}
     */
    public function add($child, $type = null, array $options = array())
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        if ($child instanceof self) {
            $child->setParent($this);
            $this->children[$child->getName()] = $child;

            // In case an unresolved child with the same name exists
            unset($this->unresolvedChildren[$child->getName()]);

            return $this;
        }

        if (!is_string($child) && !is_int($child)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($child, 'string, integer or Symfony_Component_Form_FormBuilder');
        }

        if (null !== $type && !is_string($type) && !$type instanceof Symfony_Component_Form_FormTypeInterface) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($type, 'string or Symfony_Component_Form_FormTypeInterface');
        }

        // Add to "children" to maintain order
        $this->children[$child] = null;
        $this->unresolvedChildren[$child] = array(
            'type'    => $type,
            'options' => $options,
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create($name, $type = null, array $options = array())
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        if (null === $type && null === $this->getDataClass()) {
            $type = 'text';
        }

        if (null !== $type) {
            return $this->getFormFactory()->createNamedBuilder($name, $type, null, $options, $this);
        }

        return $this->getFormFactory()->createBuilderForProperty($this->getDataClass(), $name, null, $options, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        if (isset($this->unresolvedChildren[$name])) {
            return $this->resolveChild($name);
        }

        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new Symfony_Component_Form_Exception_Exception(sprintf('The child with the name "%s" does not exist.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        unset($this->unresolvedChildren[$name]);

        if (array_key_exists($name, $this->children)) {
            if ($this->children[$name] instanceof self) {
                $this->children[$name]->setParent(null);
            }
            unset($this->children[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        if (isset($this->unresolvedChildren[$name])) {
            return true;
        }

        if (isset($this->children[$name])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        $this->resolveChildren();

        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        return count($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfig()
    {
        $config = parent::getFormConfig();

        $config->parent = null;
        $config->children = array();
        $config->unresolvedChildren = array();

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        $this->resolveChildren();

        $form = new Symfony_Component_Form_Form($this->getFormConfig());

        foreach ($this->children as $child) {
            $form->add($child->getForm());
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(Symfony_Component_Form_FormBuilderInterface $parent = null)
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent()
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        return null !== $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        return new ArrayIterator($this->children);
    }

    /**
     * Returns the types used by this builder.
     *
     * @return Symfony_Component_Form_FormTypeInterface[] An array of FormTypeInterface
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Form_FormConfigInterface::getType()} instead.
     *
     * @throws Symfony_Component_Form_Exception_BadMethodCallException If the builder was turned into a {@link FormConfigInterface}
     *                                via {@link getFormConfig()}.
     */
    public function getTypes()
    {
        trigger_error('getTypes() is deprecated since version 2.1 and will be removed in 2.3. Use getConfig() and Symfony_Component_Form_FormConfigInterface::getType() instead.', E_USER_DEPRECATED);

        if ($this->locked) {
            throw new Symfony_Component_Form_Exception_BadMethodCallException('FormBuilder methods cannot be accessed anymore once the builder is turned into a FormConfigInterface instance.');
        }

        $types = array();

        for ($type = $this->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($types, $type->getInnerType());
        }

        return $types;
    }

    /**
     * Converts an unresolved child into a {@link Symfony_Component_Form_FormBuilder} instance.
     *
     * @param  string $name The name of the unresolved child.
     *
     * @return Symfony_Component_Form_FormBuilder The created instance.
     */
    private function resolveChild($name)
    {
        $info = $this->unresolvedChildren[$name];
        $child = $this->create($name, $info['type'], $info['options']);
        $this->children[$name] = $child;
        unset($this->unresolvedChildren[$name]);

        return $child;
    }

    /**
     * Converts all unresolved children into {@link Symfony_Component_Form_FormBuilder} instances.
     */
    private function resolveChildren()
    {
        foreach ($this->unresolvedChildren as $name => $info) {
            $this->children[$name] = $this->create($name, $info['type'], $info['options']);
        }

        $this->unresolvedChildren = array();
    }
}
