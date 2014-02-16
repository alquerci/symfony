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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Tests_Extension_Validator_Constraints_FormValidatorTest extends PHPUnit_Framework_TestCase
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
    private $serverParams;

    /**
     * @var Symfony_Component_Form_Extension_Validator_Constraints_FormValidator
     */
    private $validator;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_Event')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->serverParams = $this->getMock(
            'Symfony_Component_Form_Extension_Validator_Util_ServerParams',
            array('getNormalizedIniPostMaxSize', 'getContentLength')
        );
        $this->validator = new Symfony_Component_Form_Extension_Validator_Constraints_FormValidator($this->serverParams);
    }

    public function testValidate()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $options = array('validation_groups' => array('group1', 'group2'));
        $form = $this->getBuilder('name', 'stdClass', $options)
            ->setData($object)
            ->getForm();

        $context->expects($this->at(0))
            ->method('validate')
            ->with($object, 'data', 'group1', true);
        $context->expects($this->at(1))
            ->method('validate')
            ->with($object, 'data', 'group2', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testValidateConstraints()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $constraint1 = new Symfony_Component_Validator_Constraints_NotNull(array('groups' => array('group1', 'group2')));
        $constraint2 = new Symfony_Component_Validator_Constraints_NotBlank(array('groups' => 'group2'));

        $options = array(
            'validation_groups' => array('group1', 'group2'),
            'constraints' => array($constraint1, $constraint2),
        );
        $form = $this->getBuilder('name', 'stdClass', $options)
            ->setData($object)
            ->getForm();

        // First default constraints
        $context->expects($this->at(0))
            ->method('validate')
            ->with($object, 'data', 'group1', true);
        $context->expects($this->at(1))
            ->method('validate')
            ->with($object, 'data', 'group2', true);

        // Then custom constraints
        $context->expects($this->at(2))
            ->method('validateValue')
            ->with($object, $constraint1, 'data', 'group1');
        $context->expects($this->at(3))
            ->method('validateValue')
            ->with($object, $constraint2, 'data', 'group2');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testDontValidateIfParentWithoutCascadeValidation()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');

        $parent = $this->getBuilder('parent', null, array('cascade_validation' => false))
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $options = array('validation_groups' => array('group1', 'group2'));
        $form = $this->getBuilder('name', 'stdClass', $options)->getForm();
        $parent->add($form);

        $form->setData($object);

        $context->expects($this->never())
            ->method('validate');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testValidateConstraintsEvenIfNoCascadeValidation()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $constraint1 = new Symfony_Component_Validator_Constraints_NotNull(array('groups' => array('group1', 'group2')));
        $constraint2 = new Symfony_Component_Validator_Constraints_NotBlank(array('groups' => 'group2'));

        $parent = $this->getBuilder('parent', null, array('cascade_validation' => false))
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $options = array(
            'validation_groups' => array('group1', 'group2'),
            'constraints' => array($constraint1, $constraint2),
        );
        $form = $this->getBuilder('name', 'stdClass', $options)
            ->setData($object)
            ->getForm();
        $parent->add($form);

        $context->expects($this->at(0))
            ->method('validateValue')
            ->with($object, $constraint1, 'data', 'group1');
        $context->expects($this->at(1))
            ->method('validateValue')
            ->with($object, $constraint2, 'data', 'group2');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testDontValidateIfNotSynchronized()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');

        $form = $this->getBuilder('name', 'stdClass', array(
                'invalid_message' => 'invalid_message_key',
                // Invalid message parameters must be supported, because the
                // invalid message can be a translation key
                // see https://github.com/symfony/symfony/issues/5144
                'invalid_message_parameters' => array('{{ foo }}' => 'bar'),
            ))
            ->setData($object)
            ->addViewTransformer(new Symfony_Component_Form_CallbackTransformer(
                create_function('$data', 'return $data;'),
                create_function('', 'throw new Symfony_Component_Form_Exception_TransformationFailedException();')
            ))
            ->getForm();

        // Launch transformer
        $form->bind('foo');

        $context->expects($this->never())
            ->method('validate');

        $context->expects($this->once())
            ->method('addViolation')
            ->with(
                'invalid_message_key',
                array('{{ value }}' => 'foo', '{{ foo }}' => 'bar'),
                'foo'
            );
        $context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testDontValidateConstraintsIfNotSynchronized()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $constraint1 = $this->getMock('Symfony_Component_Validator_Constraint');
        $constraint2 = $this->getMock('Symfony_Component_Validator_Constraint');

        $options = array(
            'validation_groups' => array('group1', 'group2'),
            'constraints' => array($constraint1, $constraint2),
        );
        $form = $this->getBuilder('name', 'stdClass', $options)
            ->setData($object)
            ->addViewTransformer(new Symfony_Component_Form_CallbackTransformer(
                create_function('$data', 'return $data;'),
                create_function('', 'throw new Symfony_Component_Form_Exception_TransformationFailedException();')
            ))
            ->getForm();

        // Launch transformer
        $form->bind(array());

        $context->expects($this->never())
            ->method('validate');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    // https://github.com/symfony/symfony/issues/4359
    public function testDontMarkInvalidIfAnyChildIsNotSynchronized()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');

        $failingTransformer = new Symfony_Component_Form_CallbackTransformer(
            create_function('$data', 'return $data;'),
            create_function('', 'throw new Symfony_Component_Form_Exception_TransformationFailedException();')
        );

        $form = $this->getBuilder('name', 'stdClass')
            ->setData($object)
            ->addViewTransformer($failingTransformer)
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->add(
                $this->getBuilder('child')
                    ->addViewTransformer($failingTransformer)
            )
            ->getForm();

        // Launch transformer
        $form->bind(array('child' => 'foo'));

        $context->expects($this->never())
            ->method('addViolation');
        $context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testHandleCallbackValidationGroups()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $options = array('validation_groups' => array($this, 'getValidationGroups'));
        $form = $this->getBuilder('name', 'stdClass', $options)
            ->setData($object)
            ->getForm();

        $context->expects($this->at(0))
            ->method('validate')
            ->with($object, 'data', 'group1', true);
        $context->expects($this->at(1))
            ->method('validate')
            ->with($object, 'data', 'group2', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testHandleClosureValidationGroups()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $options = array('validation_groups' => create_function('Symfony_Component_Form_FormInterface $form', '
            return array("group1", "group2");
        '));
        $form = $this->getBuilder('name', 'stdClass', $options)
            ->setData($object)
            ->getForm();

        $context->expects($this->at(0))
            ->method('validate')
            ->with($object, 'data', 'group1', true);
        $context->expects($this->at(1))
            ->method('validate')
            ->with($object, 'data', 'group2', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testUseInheritedValidationGroup()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');

        $parentOptions = array(
            'validation_groups' => 'group',
            'cascade_validation' => true,
        );
        $parent = $this->getBuilder('parent', null, $parentOptions)
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $form = $this->getBuilder('name', 'stdClass')->getForm();
        $parent->add($form);

        $form->setData($object);

        $context->expects($this->once())
            ->method('validate')
            ->with($object, 'data', 'group', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testUseInheritedCallbackValidationGroup()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');

        $parentOptions = array(
            'validation_groups' => array($this, 'getValidationGroups'),
            'cascade_validation' => true,
        );
        $parent = $this->getBuilder('parent', null, $parentOptions)
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $form = $this->getBuilder('name', 'stdClass')->getForm();
        $parent->add($form);

        $form->setData($object);

        $context->expects($this->at(0))
            ->method('validate')
            ->with($object, 'data', 'group1', true);
        $context->expects($this->at(1))
            ->method('validate')
            ->with($object, 'data', 'group2', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testUseInheritedClosureValidationGroup()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');

        $parentOptions = array(
            'validation_groups' => create_function('Symfony_Component_Form_FormInterface $form', '
                return array("group1", "group2");
            '),
            'cascade_validation' => true,
        );
        $parent = $this->getBuilder('parent', null, $parentOptions)
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $form = $this->getBuilder('name', 'stdClass')->getForm();
        $parent->add($form);

        $form->setData($object);

        $context->expects($this->at(0))
            ->method('validate')
            ->with($object, 'data', 'group1', true);
        $context->expects($this->at(1))
            ->method('validate')
            ->with($object, 'data', 'group2', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testAppendPropertyPath()
    {
        $context = $this->getMockExecutionContext();
        $object = $this->getMock('stdClass');
        $form = $this->getBuilder('name', 'stdClass')
            ->setData($object)
            ->getForm();

        $context->expects($this->once())
            ->method('validate')
            ->with($object, 'data', 'Default', true);

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testDontWalkScalars()
    {
        $context = $this->getMockExecutionContext();

        $form = $this->getBuilder()
            ->setData('scalar')
            ->getForm();

        $context->expects($this->never())
            ->method('validate');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function testViolationIfExtraData()
    {
        $context = $this->getMockExecutionContext();

        $form = $this->getBuilder('parent', null, array('extra_fields_message' => 'Extra!'))
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->add($this->getBuilder('child'))
            ->getForm();

        $form->bind(array('foo' => 'bar'));

        $context->expects($this->once())
            ->method('addViolation')
            ->with(
                'Extra!',
                array('{{ extra_fields }}' => 'foo'),
                array('foo' => 'bar')
            );
        $context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    /**
     * @dataProvider getPostMaxSizeFixtures
     */
    public function testPostMaxSizeViolation($contentLength, $iniMax, $nbViolation, array $params = array())
    {
        $this->serverParams->expects($this->once())
            ->method('getContentLength')
            ->will($this->returnValue($contentLength));
        $this->serverParams->expects($this->any())
            ->method('getNormalizedIniPostMaxSize')
            ->will($this->returnValue($iniMax));

        $context = $this->getMockExecutionContext();
        $options = array('post_max_size_message' => 'Max {{ max }}!');
        $form = $this->getBuilder('name', null, $options)->getForm();

        for ($i = 0; $i < $nbViolation; ++$i) {
            if (0 === $i && count($params) > 0) {
                $context->expects($this->at($i))
                    ->method('addViolation')
                    ->with($options['post_max_size_message'], $params);
            } else {
                $context->expects($this->at($i))
                    ->method('addViolation');
            }
        }

        $context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    public function getPostMaxSizeFixtures()
    {
        return array(
            array(pow(1024, 3) + 1, '1G', 1, array('{{ max }}' => '1G')),
            array(pow(1024, 3), '1G', 0),
            array(pow(1024, 2) + 1, '1M', 1, array('{{ max }}' => '1M')),
            array(pow(1024, 2), '1M', 0),
            array(1024 + 1, '1K', 1, array('{{ max }}' => '1K')),
            array(1024, '1K', 0),
            array(null, '1K', 0),
            array(1024, '', 0),
        );
    }

    public function testNoViolationIfNotRoot()
    {
        $this->serverParams->expects($this->once())
            ->method('getContentLength')
            ->will($this->returnValue(1025));
        $this->serverParams->expects($this->never())
            ->method('getNormalizedIniPostMaxSize');

        $context = $this->getMockExecutionContext();
        $parent = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $form = $this->getForm();
        $parent->add($form);

        $context->expects($this->never())
            ->method('addViolation');
        $context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->initialize($context);
        $this->validator->validate($form, new Symfony_Component_Form_Extension_Validator_Constraints_Form());
    }

    /**
     * Access has to be public, as this method is called via callback array
     * in {@link testValidateFormDataCanHandleCallbackValidationGroups()}
     * and {@link testValidateFormDataUsesInheritedCallbackValidationGroup()}
     */
    public function getValidationGroups(Symfony_Component_Form_FormInterface $form)
    {
        return array('group1', 'group2');
    }

    private function getMockExecutionContext()
    {
        return $this->getMock('Symfony_Component_Validator_ExecutionContextInterface');
    }

    /**
     * @param string $name
     * @param string $dataClass
     * @param array  $options
     *
     * @return Symfony_Component_Form_FormBuilder
     */
    private function getBuilder($name = 'name', $dataClass = null, array $options = array())
    {
        $options = array_replace(array(
            'constraints' => array(),
            'invalid_message_parameters' => array(),
        ), $options);

        return new Symfony_Component_Form_FormBuilder($name, $dataClass, $this->dispatcher, $this->factory, $options);
    }

    private function getForm($name = 'name', $dataClass = null)
    {
        return $this->getBuilder($name, $dataClass)->getForm();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataMapper()
    {
        return $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }
}
