<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_EventListener_FixRadioInputListenerTest extends PHPUnit_Framework_TestCase
{
    private $listener;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        parent::setUp();

        $list = new Symfony_Component_Form_Extension_Core_ChoiceList_SimpleChoiceList(array(0 => 'A', 1 => 'B'));
        $this->listener = new Symfony_Component_Form_Extension_Core_EventListener_FixRadioInputListener($list);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->listener = null;
    }

    public function testFixRadio()
    {
        $data = '1';
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $event = new Symfony_Component_Form_FormEvent($form, $data);

        $this->listener->preBind($event);

        $this->assertEquals(array(1 => '1'), $event->getData());
    }

    public function testFixZero()
    {
        $data = '0';
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $event = new Symfony_Component_Form_FormEvent($form, $data);

        $this->listener->preBind($event);

        $this->assertEquals(array(0 => '0'), $event->getData());
    }

    public function testIgnoreEmptyString()
    {
        $data = '';
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $event = new Symfony_Component_Form_FormEvent($form, $data);

        $this->listener->preBind($event);

        $this->assertEquals(array(), $event->getData());
    }
}
