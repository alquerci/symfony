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
 * Resize a collection form element based on the data sent from the client.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $options;

    /**
     * Whether children could be added to the group
     * @var Boolean
     */
    protected $allowAdd;

    /**
     * Whether children could be removed from the group
     * @var Boolean
     */
    protected $allowDelete;

    public function __construct($type, array $options = array(), $allowAdd = false, $allowDelete = false)
    {
        $this->type = $type;
        $this->allowAdd = $allowAdd;
        $this->allowDelete = $allowDelete;
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_Form_FormEvents::PRE_SET_DATA => 'preSetData',
            Symfony_Component_Form_FormEvents::PRE_BIND => 'preBind',
            // (MergeCollectionListener, MergeDoctrineCollectionListener)
            Symfony_Component_Form_FormEvents::BIND => array('onBind', 50),
        );
    }

    public function preSetData(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($data, 'array or (Traversable and ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {
            $form->add($name, $this->type, array_replace(array(
                'property_path' => '['.$name.']',
            ), $this->options));
        }
    }

    public function preBind(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data || '' === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($data, 'array or (Traversable and ArrayAccess)');
        }

        // Remove all empty rows
        if ($this->allowDelete) {
            foreach ($form as $name => $child) {
                if (!isset($data[$name])) {
                    $form->remove($name);
                }
            }
        }

        // Add all additional rows
        if ($this->allowAdd) {
            foreach ($data as $name => $value) {
                if (!$form->has($name)) {
                    $form->add($name, $this->type, array_replace(array(
                        'property_path' => '['.$name.']',
                    ), $this->options));
                }
            }
        }
    }

    public function onBind(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($data, 'array or (Traversable and ArrayAccess)');
        }

        // The data mapper only adds, but does not remove items, so do this
        // here
        if ($this->allowDelete) {
            foreach ($data as $name => $child) {
                if (!$form->has($name)) {
                    unset($data[$name]);
                }
            }
        }

        $event->setData($data);
    }
}
