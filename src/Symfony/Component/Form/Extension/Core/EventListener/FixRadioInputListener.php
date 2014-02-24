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
 * Takes care of converting the input from a single radio button
 * to an array.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_EventListener_FixRadioInputListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
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
        $value = $event->getData();
        $index = current($this->choiceList->getIndicesForValues(array($value)));

        $event->setData(false !== $index ? array($index => $value) : array());
    }

    public static function getSubscribedEvents()
    {
        return array(Symfony_Component_Form_FormEvents::PRE_BIND => 'preBind');
    }
}
