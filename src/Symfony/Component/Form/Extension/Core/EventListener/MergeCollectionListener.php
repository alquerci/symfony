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
class Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    /**
     * Whether elements may be added to the collection
     * @var Boolean
     */
    private $allowAdd;

    /**
     * Whether elements may be removed from the collection
     * @var Boolean
     */
    private $allowDelete;

    /**
     * Creates a new listener.
     *
     * @param Boolean $allowAdd Whether values might be added to the
     *                                collection.
     * @param Boolean $allowDelete Whether values might be removed from the
     *                                collection.
     */
    public function __construct($allowAdd = false, $allowDelete = false)
    {
        $this->allowAdd = $allowAdd;
        $this->allowDelete = $allowDelete;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_Form_FormEvents::BIND => 'onBind',
        );
    }

    public function onBind(Symfony_Component_Form_FormEvent $event)
    {
        $dataToMergeInto = $event->getForm()->getNormData();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($data, 'array or (Traversable and ArrayAccess)');
        }

        if (null !== $dataToMergeInto && !is_array($dataToMergeInto) && !($dataToMergeInto instanceof Traversable && $dataToMergeInto instanceof ArrayAccess)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($dataToMergeInto, 'array or (Traversable and ArrayAccess)');
        }

        // If we are not allowed to change anything, return immediately
        if ((!$this->allowAdd && !$this->allowDelete) || $data === $dataToMergeInto) {
            $event->setData($dataToMergeInto);

            return;
        }

        if (!$dataToMergeInto) {
            // No original data was set. Set it if allowed
            if ($this->allowAdd) {
                $dataToMergeInto = $data;
            }
        } else {
            // Calculate delta
            $itemsToAdd = is_object($data) ? clone $data : $data;
            $itemsToDelete = array();

            foreach ($dataToMergeInto as $beforeKey => $beforeItem) {
                foreach ($data as $afterKey => $afterItem) {
                    if ($afterItem === $beforeItem) {
                        // Item found, next original item
                        unset($itemsToAdd[$afterKey]);
                        continue 2;
                    }
                }

                // Item not found, remember for deletion
                $itemsToDelete[] = $beforeKey;
            }

            // Remove deleted items before adding to free keys that are to be
            // replaced
            if ($this->allowDelete) {
                foreach ($itemsToDelete as $key) {
                    unset($dataToMergeInto[$key]);
                }
            }

            // Add remaining items
            if ($this->allowAdd) {
                foreach ($itemsToAdd as $key => $item) {
                    if (!isset($dataToMergeInto[$key])) {
                        $dataToMergeInto[$key] = $item;
                    } else {
                        $dataToMergeInto[] = $item;
                    }
                }
            }
        }

        $event->setData($dataToMergeInto);
    }
}
