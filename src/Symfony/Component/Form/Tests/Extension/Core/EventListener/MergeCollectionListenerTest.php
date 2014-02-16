<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Form_Tests_Extension_Core_EventListener_MergeCollectionListenerTest extends PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $factory;
    protected $form;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->form = $this->getForm('axes');
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->form = null;
    }

    abstract protected function getBuilder($name = 'name');

    protected function getForm($name = 'name', $propertyPath = null)
    {
        $propertyPath = $propertyPath ? $propertyPath : $name;

        return $this->getBuilder($name)->setAttribute('property_path', $propertyPath)->getForm();
    }

    protected function getMockForm()
    {
        return $this->getMock('Symfony_Component_Form_Test_FormInterface');
    }

    public function getBooleanMatrix1()
    {
        return array(
            array(true),
            array(false),
        );
    }

    public function getBooleanMatrix2()
    {
        return array(
            array(true, true),
            array(true, false),
            array(false, true),
            array(false, false),
        );
    }

    abstract protected function getData(array $data);

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testAddExtraEntriesIfAllowAdd($allowDelete)
    {
        $originalData = $this->getData(array(1 => 'second'));
        $newData = $this->getData(array(0 => 'first', 1 => 'second', 2 => 'third'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener(true, $allowDelete);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        // The original object was modified
        if (is_object($originalData)) {
            $this->assertSame($originalData, $event->getData());
        }

        // The original object matches the new object
        $this->assertEquals($newData, $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testAddExtraEntriesIfAllowAddDontOverwriteExistingIndices($allowDelete)
    {
        $originalData = $this->getData(array(1 => 'first'));
        $newData = $this->getData(array(0 => 'first', 1 => 'second'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener(true, $allowDelete);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        // The original object was modified
        if (is_object($originalData)) {
            $this->assertSame($originalData, $event->getData());
        }

        // The original object matches the new object
        $this->assertEquals($this->getData(array(1 => 'first', 2 => 'second')), $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testDoNothingIfNotAllowAdd($allowDelete)
    {
        $originalDataArray = array(1 => 'second');
        $originalData = $this->getData($originalDataArray);
        $newData = $this->getData(array(0 => 'first', 1 => 'second', 2 => 'third'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener(false, $allowDelete);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        // We still have the original object
        if (is_object($originalData)) {
            $this->assertSame($originalData, $event->getData());
        }

        // Nothing was removed
        $this->assertEquals($this->getData($originalDataArray), $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testRemoveMissingEntriesIfAllowDelete($allowAdd)
    {
        $originalData = $this->getData(array(0 => 'first', 1 => 'second', 2 => 'third'));
        $newData = $this->getData(array(1 => 'second'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener($allowAdd, true);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        // The original object was modified
        if (is_object($originalData)) {
            $this->assertSame($originalData, $event->getData());
        }

        // The original object matches the new object
        $this->assertEquals($newData, $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testDoNothingIfNotAllowDelete($allowAdd)
    {
        $originalDataArray = array(0 => 'first', 1 => 'second', 2 => 'third');
        $originalData = $this->getData($originalDataArray);
        $newData = $this->getData(array(1 => 'second'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener($allowAdd, false);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        // We still have the original object
        if (is_object($originalData)) {
            $this->assertSame($originalData, $event->getData());
        }

        // Nothing was removed
        $this->assertEquals($this->getData($originalDataArray), $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix2
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testRequireArrayOrTraversable($allowAdd, $allowDelete)
    {
        $newData = 'no array or traversable';
        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener($allowAdd, $allowDelete);
        $listener->onBind($event);
    }

    public function testDealWithNullData()
    {
        $originalData = $this->getData(array(0 => 'first', 1 => 'second', 2 => 'third'));
        $newData = null;

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener(false, false);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        $this->assertSame($originalData, $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testDealWithNullOriginalDataIfAllowAdd($allowDelete)
    {
        $originalData = null;
        $newData = $this->getData(array(0 => 'first', 1 => 'second', 2 => 'third'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener(true, $allowDelete);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        $this->assertSame($newData, $event->getData());
    }

    /**
     * @dataProvider getBooleanMatrix1
     */
    public function testDontDealWithNullOriginalDataIfNotAllowAdd($allowDelete)
    {
        $originalData = null;
        $newData = $this->getData(array(0 => 'first', 1 => 'second', 2 => 'third'));

        $listener = new Symfony_Component_Form_Extension_Core_EventListener_MergeCollectionListener(false, $allowDelete);

        $this->form->setData($originalData);

        $event = new Symfony_Component_Form_FormEvent($this->form, $newData);
        $listener->onBind($event);

        $this->assertNull($event->getData());
    }
}
