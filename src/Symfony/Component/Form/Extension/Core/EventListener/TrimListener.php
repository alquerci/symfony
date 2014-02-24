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
 * Trims string data
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_EventListener_TrimListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public function preBind(Symfony_Component_Form_FormEvent $event)
    {
        $data = $event->getData();

        if (!is_string($data)) {
            return;
        }

        if (null !== $result = @preg_replace('/^[\pZ\p{Cc}]+|[\pZ\p{Cc}]+$/u', '', $data)) {
            $event->setData($result);
        } else {
            $event->setData(trim($data));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(Symfony_Component_Form_FormEvents::PRE_BIND => 'preBind');
    }
}
