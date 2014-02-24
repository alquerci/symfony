<?php

    /*
    * This file is part of the Symfony package.
    *
    * (c) Fabien Potencier <fabien@symfony.com>
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
    */

abstract class Symfony_Component_Form_Tests_AbstractFormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_EventDispatcher_EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Symfony_Component_Form_FormFactoryInterface
     */
    protected $factory;

    /**
     * @var Symfony_Component_Form_FormInterface
     */
    protected $form;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        // We need an actual dispatcher to bind the deprecated
        // bindRequest() method
        $this->dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->form = $this->createForm();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->form = null;
    }

    /**
     * @return Symfony_Component_Form_FormInterface
     */
    abstract protected function createForm();

    /**
     * @param string                   $name
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher
     * @param string                   $dataClass
     *
     * @return Symfony_Component_Form_FormBuilder
     */
    protected function getBuilder($name = 'name', Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null, $dataClass = null)
    {
        return new Symfony_Component_Form_FormBuilder($name, $dataClass, $dispatcher ?: $this->dispatcher, $this->factory);
    }

    /**
     * @param  string $name
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockForm($name = 'name')
    {
        $form = $this->getMock('Symfony_Component_Form_Test_FormInterface');

        $form->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $form;
    }

    /**
     * @param  string $name
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValidForm($name)
    {
        $form = $this->getMockForm($name);

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        return $form;
    }

    /**
     * @param  string $name
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInvalidForm($name)
    {
        $form = $this->getMockForm($name);

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(false));

        return $form;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataMapper()
    {
        return $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataTransformer()
    {
        return $this->getMock('Symfony_Component_Form_DataTransformerInterface');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormValidator()
    {
        return $this->getMock('Symfony_Component_Form_FormValidatorInterface');
    }
}
