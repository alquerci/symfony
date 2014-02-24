<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_EventListener_FixUrlProtocolListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }
    }

    public function testFixHttpUrl()
    {
        $data = "www.symfony.com";
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $event = new Symfony_Component_Form_FormEvent($form, $data);

        $filter = new Symfony_Component_Form_Extension_Core_EventListener_FixUrlProtocolListener('http');
        $filter->onBind($event);

        $this->assertEquals('http://www.symfony.com', $event->getData());
    }

    public function testSkipKnownUrl()
    {
        $data = "http://www.symfony.com";
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $event = new Symfony_Component_Form_FormEvent($form, $data);

        $filter = new Symfony_Component_Form_Extension_Core_EventListener_FixUrlProtocolListener('http');
        $filter->onBind($event);

        $this->assertEquals('http://www.symfony.com', $event->getData());
    }

    public function testSkipOtherProtocol()
    {
        $data = "ftp://www.symfony.com";
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $event = new Symfony_Component_Form_FormEvent($form, $data);

        $filter = new Symfony_Component_Form_Extension_Core_EventListener_FixUrlProtocolListener('http');
        $filter->onBind($event);

        $this->assertEquals('ftp://www.symfony.com', $event->getData());
    }
}
