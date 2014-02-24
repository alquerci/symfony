<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataMapper_PropertyPathMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_Form_Extension_Core_DataMapper_PropertyPathMapper
     */
    private $mapper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $propertyAccessor;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_Event')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_PropertyAccess_PropertyAccess')) {
            $this->markTestSkipped('The "PropertyAccess" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->propertyAccessor = $this->getMock('Symfony_Component_PropertyAccess_PropertyAccessorInterface');
        $this->mapper = new Symfony_Component_Form_Extension_Core_DataMapper_PropertyPathMapper($this->propertyAccessor);
    }

    /**
     * @param $path
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getPropertyPath($path)
    {
        return $this->getMockBuilder('Symfony_Component_PropertyAccess_PropertyPath')
            ->setConstructorArgs(array($path))
            ->setMethods(array('getValue', 'setValue'))
            ->getMock();
    }

    /**
     * @param Symfony_Component_Form_FormConfigInterface $config
     * @param Boolean $synchronized
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getForm(Symfony_Component_Form_FormConfigInterface $config, $synchronized = true)
    {
        $form = $this->getMockBuilder('Symfony_Component_Form_Form')
            ->setConstructorArgs(array($config))
            ->setMethods(array('isSynchronized'))
            ->getMock();

        $form->expects($this->any())
            ->method('isSynchronized')
            ->will($this->returnValue($synchronized));

        return $form;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataMapper()
    {
        return $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }

    public function testMapDataToFormsPassesObjectRefIfByReference()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->once())
            ->method('getValue')
            ->with($car, $propertyPath)
            ->will($this->returnValue($engine));

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $form = $this->getForm($config);

        $this->mapper->mapDataToForms($car, array($form));

        // Can't use isIdentical() above because mocks always clone their
        // arguments which can't be disabled in PHPUnit 3.6
        $this->assertSame($engine, $form->getData());
    }

    public function testMapDataToFormsPassesObjectCloneIfNotByReference()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->once())
            ->method('getValue')
            ->with($car, $propertyPath)
            ->will($this->returnValue($engine));

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(false);
        $config->setPropertyPath($propertyPath);
        $form = $this->getForm($config);

        $this->mapper->mapDataToForms($car, array($form));

        $this->assertNotSame($engine, $form->getData());
        $this->assertEquals($engine, $form->getData());
    }

    public function testMapDataToFormsIgnoresEmptyPropertyPath()
    {
        $car = new stdClass();

        $config = new Symfony_Component_Form_FormConfigBuilder(null, 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $form = $this->getForm($config);

        $this->assertNull($form->getPropertyPath());

        $this->mapper->mapDataToForms($car, array($form));

        $this->assertNull($form->getData());
    }

    public function testMapDataToFormsIgnoresUnmapped()
    {
        $car = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('getValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setMapped(false);
        $config->setPropertyPath($propertyPath);
        $form = $this->getForm($config);

        $this->mapper->mapDataToForms($car, array($form));

        $this->assertNull($form->getData());
    }

    public function testMapDataToFormsIgnoresEmptyData()
    {
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('getValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $form = $this->getForm($config);

        $this->mapper->mapDataToForms(null, array($form));

        $this->assertNull($form->getData());
    }

    public function testMapDataToFormsSkipsVirtualForms()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->once())
            ->method('getValue')
            ->with($car, $propertyPath)
            ->will($this->returnValue($engine));

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setVirtual(true);
        $config->setCompound(true);
        $config->setDataMapper($this->getDataMapper());
        $form = $this->getForm($config);

        $config = new Symfony_Component_Form_FormConfigBuilder('engine', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $child = $this->getForm($config);

        $form->add($child);

        $this->mapper->mapDataToForms($car, array($form));

        $this->assertNull($form->getData());
        $this->assertSame($engine, $child->getData());
    }

    public function testMapFormsToDataWritesBackIfNotByReference()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with($car, $propertyPath, $engine);

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(false);
        $config->setPropertyPath($propertyPath);
        $config->setData($engine);
        $form = $this->getForm($config);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataWritesBackIfByReferenceButNoReference()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with($car, $propertyPath, $engine);

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $config->setData($engine);
        $form = $this->getForm($config);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataWritesBackIfByReferenceAndReference()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        // $car already contains the reference of $engine
        $this->propertyAccessor->expects($this->once())
            ->method('getValue')
            ->with($car, $propertyPath)
            ->will($this->returnValue($engine));

        $this->propertyAccessor->expects($this->never())
            ->method('setValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $config->setData($engine);
        $form = $this->getForm($config);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataIgnoresUnmapped()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('setValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $config->setData($engine);
        $config->setMapped(false);
        $form = $this->getForm($config);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataIgnoresEmptyData()
    {
        $car = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('setValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $config->setData(null);
        $form = $this->getForm($config);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataIgnoresUnsynchronized()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('setValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $config->setData($engine);
        $form = $this->getForm($config, false);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataIgnoresDisabled()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('setValue');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($propertyPath);
        $config->setData($engine);
        $config->setDisabled(true);
        $form = $this->getForm($config);

        $this->mapper->mapFormsToData(array($form), $car);
    }

    public function testMapFormsToDataSkipsVirtualForms()
    {
        $car = new stdClass();
        $engine = new stdClass();
        $parentPath = $this->getPropertyPath('name');
        $childPath = $this->getPropertyPath('engine');

        // getValue() and setValue() must never be invoked for $parentPath

        $this->propertyAccessor->expects($this->once())
            ->method('getValue')
            ->with($car, $childPath);
        $this->propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with($car, $childPath, $engine);

        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->setPropertyPath($parentPath);
        $config->setVirtual(true);
        $config->setCompound(true);
        $config->setDataMapper($this->getDataMapper());
        $form = $this->getForm($config);

        $config = new Symfony_Component_Form_FormConfigBuilder('engine', 'stdClass', $this->dispatcher);
        $config->setByReference(true);
        $config->setPropertyPath($childPath);
        $config->setData($engine);
        $child = $this->getForm($config);

        $form->add($child);

        $this->mapper->mapFormsToData(array($form), $car);
    }
}
