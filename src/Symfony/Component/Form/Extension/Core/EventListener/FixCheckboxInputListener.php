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
 * Takes care of converting the input from a list of checkboxes to a correctly
 * indexed array.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_EventListener_FixCheckboxInputListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $choiceList;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface $choiceList
     */
    public function __construct(Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    public function preBind(Symfony_Component_Form_FormEvent $event)
    {
        $values = (array) $event->getData();
        $indices = $this->choiceList->getIndicesForValues($values);

        $event->setData(count($indices) > 0 ? array_combine($indices, $values) : array());
    }

    public static function getSubscribedEvents()
    {
        return array(Symfony_Component_Form_FormEvents::PRE_BIND => 'preBind');
    }
}
