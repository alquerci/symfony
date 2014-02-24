<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Validator_EventListener_ValidationListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $violationMapper;

    /**
     * @var Symfony_Component_Form_Extension_Validator_EventListener_ValidationListener
     */
    private $listener;

    private $message;

    private $messageTemplate;

    private $params;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_Event')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->validator = $this->getMock('Symfony_Component_Validator_ValidatorInterface');
        $this->violationMapper = $this->getMock('Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapperInterface');
        $this->listener = new Symfony_Component_Form_Extension_Validator_EventListener_ValidationListener($this->validator, $this->violationMapper);
        $this->message = 'Message';
        $this->messageTemplate = 'Message template';
        $this->params = array('foo' => 'bar');
    }

    private function getConstraintViolation($code = null)
    {
        return new Symfony_Component_Validator_ConstraintViolation($this->message, $this->messageTemplate, $this->params, null, 'prop.path', null, null, $code);
    }

    private function getBuilder($name = 'name', $propertyPath = null, $dataClass = null)
    {
        $builder = new Symfony_Component_Form_FormBuilder($name, $dataClass, $this->dispatcher, $this->factory);
        $builder->setPropertyPath(new Symfony_Component_PropertyAccess_PropertyPath($propertyPath ?: $name));
        $builder->setAttribute('error_mapping', array());
        $builder->setErrorBubbling(false);
        $builder->setMapped(true);

        return $builder;
    }

    private function getForm($name = 'name', $propertyPath = null, $dataClass = null)
    {
        return $this->getBuilder($name, $propertyPath, $dataClass)->getForm();
    }

    private function getMockForm()
    {
        return $this->getMock('Symfony_Component_Form_Test_FormInterface');
    }

    // More specific mapping tests can be found in ViolationMapperTest
    public function testMapViolation()
    {
        $violation = $this->getConstraintViolation();
        $form = $this->getForm('street');

        $this->validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array($violation)));

        $this->violationMapper->expects($this->once())
            ->method('mapViolation')
            ->with($violation, $form, false);

        $this->listener->validateForm(new Symfony_Component_Form_FormEvent($form, null));
    }

    public function testMapViolationAllowsNonSyncIfInvalid()
    {
        $violation = $this->getConstraintViolation(Symfony_Component_Form_Extension_Validator_Constraints_Form::ERR_INVALID);
        $form = $this->getForm('street');

        $this->validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array($violation)));

        $this->violationMapper->expects($this->once())
            ->method('mapViolation')
            // pass true now
            ->with($violation, $form, true);

        $this->listener->validateForm(new Symfony_Component_Form_FormEvent($form, null));
    }

    public function testValidateIgnoresNonRoot()
    {
        $form = $this->getMockForm();
        $form->expects($this->once())
            ->method('isRoot')
            ->will($this->returnValue(false));

        $this->validator->expects($this->never())
            ->method('validate');

        $this->violationMapper->expects($this->never())
            ->method('mapViolation');

        $this->listener->validateForm(new Symfony_Component_Form_FormEvent($form, null));
    }
}
