<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Fixtures_FixedFilterListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = array_merge(array(
            'preBind' => array(),
            'onBind' => array(),
            'preSetData' => array(),
        ), $mapping);
    }

    public function preBind(Symfony_Component_Form_FormEvent $event)
    {
        $data = $event->getData();

        if (isset($this->mapping['preBind'][$data])) {
            $event->setData($this->mapping['preBind'][$data]);
        }
    }

    public function onBind(Symfony_Component_Form_FormEvent $event)
    {
        $data = $event->getData();

        if (isset($this->mapping['onBind'][$data])) {
            $event->setData($this->mapping['onBind'][$data]);
        }
    }

    public function preSetData(Symfony_Component_Form_FormEvent $event)
    {
        $data = $event->getData();

        if (isset($this->mapping['preSetData'][$data])) {
            $event->setData($this->mapping['preSetData'][$data]);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_Form_FormEvents::PRE_BIND => 'preBind',
            Symfony_Component_Form_FormEvents::BIND => 'onBind',
            Symfony_Component_Form_FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }
}
