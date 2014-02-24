<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_FormType extends Symfony_Component_Form_AbstractType
{
    /**
     * @var Symfony_Component_PropertyAccess_PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(Symfony_Component_PropertyAccess_PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setRequired($options['required'])
            ->setDisabled($options['disabled'])
            ->setErrorBubbling($options['error_bubbling'])
            ->setEmptyData($options['empty_data'])
            // BC compatibility, when "property_path" could be false
            ->setPropertyPath(is_string($options['property_path']) ? $options['property_path'] : null)
            ->setMapped($options['mapped'])
            ->setByReference($options['by_reference'])
            ->setVirtual($options['virtual'])
            ->setCompound($options['compound'])
            ->setData(isset($options['data']) ? $options['data'] : null)
            ->setDataLocked(isset($options['data']))
            ->setDataMapper($options['compound'] ? new Symfony_Component_Form_Extension_Core_DataMapper_PropertyPathMapper($this->propertyAccessor) : null)
        ;

        if (false === $options['property_path']) {
            trigger_error('Setting "property_path" to "false" is deprecated since version 2.1 and will be removed in 2.3. Set "mapped" to "false" instead.', E_USER_DEPRECATED);
        }

        if ($options['trim']) {
            $builder->addEventSubscriber(new Symfony_Component_Form_Extension_Core_EventListener_TrimListener());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $name = $form->getName();
        $blockName = $options['block_name'] ?: $form->getName();
        $readOnly = $options['read_only'];
        $translationDomain = $options['translation_domain'];

        if ($view->parent) {
            if ('' === $name) {
                throw new Symfony_Component_Form_Exception_Exception('Form node with empty name can be used only as root form node.');
            }

            if ('' !== ($parentFullName = $view->parent->vars['full_name'])) {
                $id = sprintf('%s_%s', $view->parent->vars['id'], $name);
                $fullName = sprintf('%s[%s]', $parentFullName, $name);
                $uniqueBlockPrefix = sprintf('%s_%s', $view->parent->vars['unique_block_prefix'], $blockName);
            } else {
                $id = $name;
                $fullName = $name;
                $uniqueBlockPrefix = '_' . $blockName;
            }

            // Complex fields are read-only if they themselves or their parents are.
            if (!$readOnly) {
                $readOnly = $view->parent->vars['read_only'];
            }

            if (!$translationDomain) {
                $translationDomain = $view->parent->vars['translation_domain'];
            }
        } else {
            $id = $name;
            $fullName = $name;
            $uniqueBlockPrefix = '_' . $blockName;

            // Strip leading underscores and digits. These are allowed in
            // form names, but not in HTML4 ID attributes.
            // http://www.w3.org/TR/html401/struct/global.html#adef-id
            $id = ltrim($id, '_0123456789');
        }

        $blockPrefixes = array();
        for ($type = $form->getConfig()->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($blockPrefixes, $type->getName());
        }
        $blockPrefixes[] = $uniqueBlockPrefix;

        if (!$translationDomain) {
            $translationDomain = 'messages';
        }

        $view->vars = array_replace($view->vars, array(
            'form'                => $view,
            'id'                  => $id,
            'name'                => $name,
            'full_name'           => $fullName,
            'read_only'           => $readOnly,
            'errors'              => $form->getErrors(),
            'valid'               => $form->isBound() ? $form->isValid() : true,
            'value'               => $form->getViewData(),
            'data'                => $form->getNormData(),
            'disabled'            => $form->isDisabled(),
            'required'            => $form->isRequired(),
            'max_length'          => $options['max_length'],
            'pattern'             => $options['pattern'],
            'size'                => null,
            'label'               => $options['label'],
            'multipart'           => false,
            'attr'                => $options['attr'],
            'label_attr'          => $options['label_attr'],
            'compound'            => $form->getConfig()->getCompound(),
            'block_prefixes'      => $blockPrefixes,
            'unique_block_prefix' => $uniqueBlockPrefix,
            'translation_domain'  => $translationDomain,
            // Using the block name here speeds up performance in collection
            // forms, where each entry has the same full block name.
            // Including the type is important too, because if rows of a
            // collection form have different types (dynamically), they should
            // be rendered differently.
            // https://github.com/symfony/symfony/issues/5038
            'cache_key'           => $uniqueBlockPrefix . '_' . $form->getConfig()->getType()->getName(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $multipart = false;

        foreach ($view->children as $child) {
            if ($child->vars['multipart']) {
                $multipart = true;
                break;
            }
        }

        $view->vars['multipart'] = $multipart;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        // Derive "data_class" option from passed "data" object
        $dataClass = function (Symfony_Component_OptionsResolver_Options $options) {
            return isset($options['data']) && is_object($options['data']) ? get_class($options['data']) : null;
        };

        // Derive "empty_data" closure from "data_class" option
        $emptyData = function (Symfony_Component_OptionsResolver_Options $options) {
            $class = $options['data_class'];

            if (null !== $class) {
                return function (Symfony_Component_Form_FormInterface $form) use ($class) {
                    return $form->isEmpty() && !$form->isRequired() ? null : new $class();
                };
            }

            return function (Symfony_Component_Form_FormInterface $form) {
                return $form->getConfig()->getCompound() ? array() : '';
            };
        };

        // For any form that is not represented by a single HTML control,
        // errors should bubble up by default
        $errorBubbling = function (Symfony_Component_OptionsResolver_Options $options) {
            return $options['compound'];
        };

        // BC clause: former property_path=false now equals mapped=false
        $mapped = function (Symfony_Component_OptionsResolver_Options $options) {
            return false !== $options['property_path'];
        };

        // If data is given, the form is locked to that data
        // (independent of its value)
        $resolver->setOptional(array(
            'data',
        ));

        $resolver->setDefaults(array(
            'block_name'         => null,
            'data_class'         => $dataClass,
            'empty_data'         => $emptyData,
            'trim'               => true,
            'required'           => true,
            'read_only'          => false,
            'disabled'           => false,
            'max_length'         => null,
            'pattern'            => null,
            'property_path'      => null,
            'mapped'             => $mapped,
            'by_reference'       => true,
            'error_bubbling'     => $errorBubbling,
            'label'              => null,
            'attr'               => array(),
            'label_attr'         => array(),
            'virtual'            => false,
            'compound'           => true,
            'translation_domain' => null,
        ));

        $resolver->setAllowedTypes(array(
            'attr'       => 'array',
            'label_attr' => 'array',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'form';
    }
}
