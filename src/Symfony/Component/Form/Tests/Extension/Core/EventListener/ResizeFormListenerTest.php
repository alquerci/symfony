<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_EventListener_ResizeFormListenerTest extends PHPUnit_Framework_TestCase
{
    private $dispatcher;
    private $factory;
    private $form;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->form = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->form = null;
    }

    protected function getBuilder($name = 'name')
    {
        return new Symfony_Component_Form_FormBuilder($name, null, $this->dispatcher, $this->factory);
    }

    protected function getForm($name = 'name')
    {
        return $this->getBuilder($name)->getForm();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataMapper()
    {
        return $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }

    protected function getMockForm()
    {
        return $this->getMock('Symfony_Component_Form_Test_FormInterface');
    }

    public function testPreSetDataResizesForm()
    {
        $this->form->add($this->getForm('0'));
        $this->form->add($this->getForm('1'));

        $this->factory->expects($this->at(0))
            ->method('createNamed')
            ->with(1, 'text', null, array('property_path' => '[1]', 'max_length' => 10))
            ->will($this->returnValue($this->getForm('1')));
        $this->factory->expects($this->at(1))
            ->method('createNamed')
            ->with(2, 'text', null, array('property_path' => '[2]', 'max_length' => 10))
            ->will($this->returnValue($this->getForm('2')));

        $data = array(1 => 'string', 2 => 'string');
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array('max_length' => '10'), false, false);
        $listener->preSetData($event);

        $this->assertFalse($this->form->has('0'));
        $this->assertTrue($this->form->has('1'));
        $this->assertTrue($this->form->has('2'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testPreSetDataRequiresArrayOrTraversable()
    {
        $data = 'no array or traversable';
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, false);
        $listener->preSetData($event);
    }

    public function testPreSetDataDealsWithNullData()
    {
        $this->factory->expects($this->never())->method('createNamed');

        $data = null;
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, false);
        $listener->preSetData($event);
    }

    public function testPreBindResizesUpIfAllowAdd()
    {
        $this->form->add($this->getForm('0'));

        $this->factory->expects($this->once())
            ->method('createNamed')
            ->with(1, 'text', null, array('property_path' => '[1]', 'max_length' => 10))
            ->will($this->returnValue($this->getForm('1')));

        $data = array(0 => 'string', 1 => 'string');
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array('max_length' => 10), true, false);
        $listener->preBind($event);

        $this->assertTrue($this->form->has('0'));
        $this->assertTrue($this->form->has('1'));
    }

    public function testPreBindResizesDownIfAllowDelete()
    {
        $this->form->add($this->getForm('0'));
        $this->form->add($this->getForm('1'));

        $data = array(0 => 'string');
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, true);
        $listener->preBind($event);

        $this->assertTrue($this->form->has('0'));
        $this->assertFalse($this->form->has('1'));
    }

    // fix for https://github.com/symfony/symfony/pull/493
    public function testPreBindRemovesZeroKeys()
    {
        $this->form->add($this->getForm('0'));

        $data = array();
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, true);
        $listener->preBind($event);

        $this->assertFalse($this->form->has('0'));
    }

    public function testPreBindDoesNothingIfNotAllowAddNorAllowDelete()
    {
        $this->form->add($this->getForm('0'));
        $this->form->add($this->getForm('1'));

        $data = array(0 => 'string', 2 => 'string');
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, false);
        $listener->preBind($event);

        $this->assertTrue($this->form->has('0'));
        $this->assertTrue($this->form->has('1'));
        $this->assertFalse($this->form->has('2'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testPreBindRequiresArrayOrTraversable()
    {
        $data = 'no array or traversable';
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, false);
        $listener->preBind($event);
    }

    public function testPreBindDealsWithNullData()
    {
        $this->form->add($this->getForm('1'));

        $data = null;
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, true);
        $listener->preBind($event);

        $this->assertFalse($this->form->has('1'));
    }

    // fixes https://github.com/symfony/symfony/pull/40
    public function testPreBindDealsWithEmptyData()
    {
        $this->form->add($this->getForm('1'));

        $data = '';
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, true);
        $listener->preBind($event);

        $this->assertFalse($this->form->has('1'));
    }

    public function testOnBindNormDataRemovesEntriesMissingInTheFormIfAllowDelete()
    {
        $this->form->add($this->getForm('1'));

        $data = array(0 => 'first', 1 => 'second', 2 => 'third');
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, true);
        $listener->onBind($event);

        $this->assertEquals(array(1 => 'second'), $event->getData());
    }

    public function testOnBindNormDataDoesNothingIfNotAllowDelete()
    {
        $this->form->add($this->getForm('1'));

        $data = array(0 => 'first', 1 => 'second', 2 => 'third');
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, false);
        $listener->onBind($event);

        $this->assertEquals($data, $event->getData());
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testOnBindNormDataRequiresArrayOrTraversable()
    {
        $data = 'no array or traversable';
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, false);
        $listener->onBind($event);
    }

    public function testOnBindNormDataDealsWithNullData()
    {
        $this->form->add($this->getForm('1'));

        $data = null;
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_ResizeFormListener('text', array(), false, true);
        $listener->onBind($event);

        $this->assertEquals(array(), $event->getData());
    }
}
