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
 * A data mapper using property paths to read/write data.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_DataMapper_PropertyPathMapper implements Symfony_Component_Form_DataMapperInterface
{
    /**
     * @var Symfony_Component_PropertyAccess_PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Creates a new property path mapper.
     *
     * @param Symfony_Component_PropertyAccess_PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(Symfony_Component_PropertyAccess_PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ? $propertyAccessor : Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, array $forms)
    {
        if (null === $data || array() === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($data, 'object, array or empty');
        }

        $iterator = new Symfony_Component_Form_Util_VirtualFormAwareIterator($forms);
        $iterator = new RecursiveIteratorIterator($iterator);

        foreach ($iterator as $form) {
            /* @var FormInterface $form */
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (null !== $propertyPath && $config->getMapped()) {
                $form->setData($this->propertyAccessor->getValue($data, $propertyPath));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData(array $forms, &$data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($data, 'object, array or empty');
        }

        $iterator = new Symfony_Component_Form_Util_VirtualFormAwareIterator($forms);
        $iterator = new RecursiveIteratorIterator($iterator);

        foreach ($iterator as $form) {
            /* @var FormInterface $form */
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            // Write-back is disabled if the form is not synchronized (transformation failed)
            // and if the form is disabled (modification not allowed)
            if (null !== $propertyPath && $config->getMapped() && $form->isSynchronized() && !$form->isDisabled()) {
                // If the data is identical to the value in $data, we are
                // dealing with a reference
                if (!is_object($data) || !$config->getByReference() || $form->getData() !== $this->propertyAccessor->getValue($data, $propertyPath)) {
                    $this->propertyAccessor->setValue($data, $propertyPath, $form->getData());
                }
            }
        }
    }
}
