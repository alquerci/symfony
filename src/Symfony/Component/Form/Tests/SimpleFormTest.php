<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_SimpleFormTest_Countable implements Countable
{
    private $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function count()
    {
        return $this->count;
    }
}

class Symfony_Component_Form_Tests_SimpleFormTest_Traversable implements IteratorAggregate
{
    private $iterator;

    public function __construct($count)
    {
        $this->iterator = new ArrayIterator($count > 0 ? array_fill(0, $count, 'Foo') : array());
    }

    public function getIterator()
    {
        return $this->iterator;
    }
}

class Symfony_Component_Form_Tests_SimpleFormTest extends Symfony_Component_Form_Tests_AbstractFormTest
{
    public function testDataIsInitializedToConfiguredValue()
    {
        $model = new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            'default' => 'foo',
        ));
        $view = new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            'foo' => 'bar',
        ));

        $config = new Symfony_Component_Form_FormConfigBuilder('name', null, $this->dispatcher);
        $config->addViewTransformer($view);
        $config->addModelTransformer($model);
        $config->setData('default');
        $form = new Symfony_Component_Form_Form($config);

        $this->assertSame('default', $form->getData());
        $this->assertSame('foo', $form->getNormData());
        $this->assertSame('bar', $form->getViewData());
    }

    // https://github.com/symfony/symfony/commit/d4f4038f6daf7cf88ca7c7ab089473cce5ebf7d8#commitcomment-1632879
    public function testDataIsInitializedFromBind()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(array('preSetData', 'preBind'))
            ->getMock();
        $mock->expects($this->at(0))
            ->method('preSetData');
        $mock->expects($this->at(1))
            ->method('preBind');

        $config = new Symfony_Component_Form_FormConfigBuilder('name', null, $this->dispatcher);
        $config->addEventListener(Symfony_Component_Form_FormEvents::PRE_SET_DATA, array($mock, 'preSetData'));
        $config->addEventListener(Symfony_Component_Form_FormEvents::PRE_BIND, array($mock, 'preBind'));
        $form = new Symfony_Component_Form_Form($config);

        // no call to setData() or similar where the object would be
        // initialized otherwise

        $form->bind('foobar');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_AlreadyBoundException
     */
    public function testBindThrowsExceptionIfAlreadyBound()
    {
        $this->form->bind(array());
        $this->form->bind(array());
    }

    public function testBindIsIgnoredIfDisabled()
    {
        $form = $this->getBuilder()
            ->setDisabled(true)
            ->setData('initial')
            ->getForm();

        $form->bind('new');

        $this->assertEquals('initial', $form->getData());
        $this->assertTrue($form->isBound());
    }

    public function testNeverRequiredIfParentNotRequired()
    {
        $parent = $this->getBuilder()->setRequired(false)->getForm();
        $child = $this->getBuilder()->setRequired(true)->getForm();

        $child->setParent($parent);

        $this->assertFalse($child->isRequired());
    }

    public function testRequired()
    {
        $parent = $this->getBuilder()->setRequired(true)->getForm();
        $child = $this->getBuilder()->setRequired(true)->getForm();

        $child->setParent($parent);

        $this->assertTrue($child->isRequired());
    }

    public function testNotRequired()
    {
        $parent = $this->getBuilder()->setRequired(true)->getForm();
        $child = $this->getBuilder()->setRequired(false)->getForm();

        $child->setParent($parent);

        $this->assertFalse($child->isRequired());
    }

    public function testAlwaysDisabledIfParentDisabled()
    {
        $parent = $this->getBuilder()->setDisabled(true)->getForm();
        $child = $this->getBuilder()->setDisabled(false)->getForm();

        $child->setParent($parent);

        $this->assertTrue($child->isDisabled());
    }

    public function testDisabled()
    {
        $parent = $this->getBuilder()->setDisabled(false)->getForm();
        $child = $this->getBuilder()->setDisabled(true)->getForm();

        $child->setParent($parent);

        $this->assertTrue($child->isDisabled());
    }

    public function testNotDisabled()
    {
        $parent = $this->getBuilder()->setDisabled(false)->getForm();
        $child = $this->getBuilder()->setDisabled(false)->getForm();

        $child->setParent($parent);

        $this->assertFalse($child->isDisabled());
    }

    public function testGetRootReturnsRootOfParent()
    {
        $parent = $this->getMockForm();
        $parent->expects($this->once())
            ->method('getRoot')
            ->will($this->returnValue('ROOT'));

        $this->form->setParent($parent);

        $this->assertEquals('ROOT', $this->form->getRoot());
    }

    public function testGetRootReturnsSelfIfNoParent()
    {
        $this->assertSame($this->form, $this->form->getRoot());
    }

    public function testEmptyIfEmptyArray()
    {
        $this->form->setData(array());

        $this->assertTrue($this->form->isEmpty());
    }

    public function testEmptyIfEmptyCountable()
    {
        $this->form = new Symfony_Component_Form_Form(new Symfony_Component_Form_FormConfigBuilder('name', 'Symfony_Component_Form_Tests_SimpleFormTest_Countable', $this->dispatcher));

        $this->form->setData(new Symfony_Component_Form_Tests_SimpleFormTest_Countable(0));

        $this->assertTrue($this->form->isEmpty());
    }

    public function testNotEmptyIfFilledCountable()
    {
        $this->form = new Symfony_Component_Form_Form(new Symfony_Component_Form_FormConfigBuilder('name', 'Symfony_Component_Form_Tests_SimpleFormTest_Countable', $this->dispatcher));

        $this->form->setData(new Symfony_Component_Form_Tests_SimpleFormTest_Countable(1));

        $this->assertFalse($this->form->isEmpty());
    }

    public function testEmptyIfEmptyTraversable()
    {
        $this->form = new Symfony_Component_Form_Form(new Symfony_Component_Form_FormConfigBuilder('name', 'Symfony_Component_Form_Tests_SimpleFormTest_Traversable', $this->dispatcher));

        $this->form->setData(new Symfony_Component_Form_Tests_SimpleFormTest_Traversable(0));

        $this->assertTrue($this->form->isEmpty());
    }

    public function testNotEmptyIfFilledTraversable()
    {
        $this->form = new Symfony_Component_Form_Form(new Symfony_Component_Form_FormConfigBuilder('name', 'Symfony_Component_Form_Tests_SimpleFormTest_Traversable', $this->dispatcher));

        $this->form->setData(new Symfony_Component_Form_Tests_SimpleFormTest_Traversable(1));

        $this->assertFalse($this->form->isEmpty());
    }

    public function testEmptyIfNull()
    {
        $this->form->setData(null);

        $this->assertTrue($this->form->isEmpty());
    }

    public function testEmptyIfEmptyString()
    {
        $this->form->setData('');

        $this->assertTrue($this->form->isEmpty());
    }

    public function testNotEmptyIfText()
    {
        $this->form->setData('foobar');

        $this->assertFalse($this->form->isEmpty());
    }

    public function testValidIfBound()
    {
        $form = $this->getBuilder()->getForm();
        $form->bind('foobar');

        $this->assertTrue($form->isValid());
    }

    public function testValidIfBoundAndDisabled()
    {
        $form = $this->getBuilder()->setDisabled(true)->getForm();
        $form->bind('foobar');

        $this->assertTrue($form->isValid());
    }

    /**
     * @expectedException LogicException
     */
    public function testNotValidIfNotBound()
    {
        $this->form->isValid();
    }

    public function testNotValidIfErrors()
    {
        $form = $this->getBuilder()->getForm();
        $form->bind('foobar');
        $form->addError(new Symfony_Component_Form_FormError('Error!'));

        $this->assertFalse($form->isValid());
    }

    public function testHasErrors()
    {
        $this->form->addError(new Symfony_Component_Form_FormError('Error!'));

        $this->assertCount(1, $this->form->getErrors());
    }

    public function testHasNoErrors()
    {
        $this->assertCount(0, $this->form->getErrors());
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_AlreadyBoundException
     */
    public function testSetParentThrowsExceptionIfAlreadyBound()
    {
        $this->form->bind(array());
        $this->form->setParent($this->getBuilder('parent')->getForm());
    }

    public function testBound()
    {
        $form = $this->getBuilder()->getForm();
        $form->bind('foobar');

        $this->assertTrue($form->isBound());
    }

    public function testNotBound()
    {
        $this->assertFalse($this->form->isBound());
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_AlreadyBoundException
     */
    public function testSetDataThrowsExceptionIfAlreadyBound()
    {
        $this->form->bind(array());
        $this->form->setData(null);
    }

    public function testSetDataClonesObjectIfNotByReference()
    {
        $data = new stdClass();
        $form = $this->getBuilder('name', null, 'stdClass')->setByReference(false)->getForm();
        $form->setData($data);

        $this->assertNotSame($data, $form->getData());
        $this->assertEquals($data, $form->getData());
    }

    public function testSetDataDoesNotCloneObjectIfByReference()
    {
        $data = new stdClass();
        $form = $this->getBuilder('name', null, 'stdClass')->setByReference(true)->getForm();
        $form->setData($data);

        $this->assertSame($data, $form->getData());
    }

    public function testSetDataExecutesTransformationChain()
    {
        // use real event dispatcher now
        $form = $this->getBuilder('name', new Symfony_Component_EventDispatcher_EventDispatcher())
            ->addEventSubscriber(new Symfony_Component_Form_Tests_Fixtures_FixedFilterListener(array(
            'preSetData' => array(
                'app' => 'filtered',
            ),
        )))
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'filtered' => 'norm',
        )))
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'norm' => 'client',
        )))
            ->getForm();

        $form->setData('app');

        $this->assertEquals('filtered', $form->getData());
        $this->assertEquals('norm', $form->getNormData());
        $this->assertEquals('client', $form->getViewData());
    }

    public function testSetDataExecutesViewTransformersInOrder()
    {
        $form = $this->getBuilder()
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'first' => 'second',
        )))
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'second' => 'third',
        )))
            ->getForm();

        $form->setData('first');

        $this->assertEquals('third', $form->getViewData());
    }

    public function testSetDataExecutesModelTransformersInReverseOrder()
    {
        $form = $this->getBuilder()
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'second' => 'third',
        )))
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'first' => 'second',
        )))
            ->getForm();

        $form->setData('first');

        $this->assertEquals('third', $form->getNormData());
    }

    /*
     * When there is no data transformer, the data must have the same format
     * in all three representations
     */
    public function testSetDataConvertsScalarToStringIfNoTransformer()
    {
        $form = $this->getBuilder()->getForm();

        $form->setData(1);

        $this->assertSame('1', $form->getData());
        $this->assertSame('1', $form->getNormData());
        $this->assertSame('1', $form->getViewData());
    }

    /*
     * Data in client format should, if possible, always be a string to
     * facilitate differentiation between '0' and ''
     */
    public function testSetDataConvertsScalarToStringIfOnlyModelTransformer()
    {
        $form = $this->getBuilder()
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            1 => 23,
        )))
            ->getForm();

        $form->setData(1);

        $this->assertSame(1, $form->getData());
        $this->assertSame(23, $form->getNormData());
        $this->assertSame('23', $form->getViewData());
    }

    /*
     * NULL remains NULL in app and norm format to remove the need to treat
     * empty values and NULL explicitly in the application
     */
    public function testSetDataConvertsNullToStringIfNoTransformer()
    {
        $form = $this->getBuilder()->getForm();

        $form->setData(null);

        $this->assertNull($form->getData());
        $this->assertNull($form->getNormData());
        $this->assertSame('', $form->getViewData());
    }

    public function testSetDataIsIgnoredIfDataIsLocked()
    {
        $form = $this->getBuilder()
            ->setData('default')
            ->setDataLocked(true)
            ->getForm();

        $form->setData('foobar');

        $this->assertSame('default', $form->getData());
    }

    public function testBindConvertsEmptyToNullIfNoTransformer()
    {
        $form = $this->getBuilder()->getForm();

        $form->bind('');

        $this->assertNull($form->getData());
        $this->assertNull($form->getNormData());
        $this->assertSame('', $form->getViewData());
    }

    public function testBindExecutesTransformationChain()
    {
        // use real event dispatcher now
        $form = $this->getBuilder('name', new Symfony_Component_EventDispatcher_EventDispatcher())
            ->addEventSubscriber(new Symfony_Component_Form_Tests_Fixtures_FixedFilterListener(array(
            'preBind' => array(
                'client' => 'filteredclient',
            ),
            'onBind' => array(
                'norm' => 'filterednorm',
            ),
        )))
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            // direction is reversed!
            'norm' => 'filteredclient',
            'filterednorm' => 'cleanedclient'
        )))
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            // direction is reversed!
            'app' => 'filterednorm',
        )))
            ->getForm();

        $form->bind('client');

        $this->assertEquals('app', $form->getData());
        $this->assertEquals('filterednorm', $form->getNormData());
        $this->assertEquals('cleanedclient', $form->getViewData());
    }

    public function testBindExecutesViewTransformersInReverseOrder()
    {
        $form = $this->getBuilder()
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'third' => 'second',
        )))
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'second' => 'first',
        )))
            ->getForm();

        $form->bind('first');

        $this->assertEquals('third', $form->getNormData());
    }

    public function testBindExecutesModelTransformersInOrder()
    {
        $form = $this->getBuilder()
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'second' => 'first',
        )))
            ->addModelTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'third' => 'second',
        )))
            ->getForm();

        $form->bind('first');

        $this->assertEquals('third', $form->getData());
    }

    public function testSynchronizedByDefault()
    {
        $this->assertTrue($this->form->isSynchronized());
    }

    public function testSynchronizedAfterBinding()
    {
        $this->form->bind('foobar');

        $this->assertTrue($this->form->isSynchronized());
    }

    public function testNotSynchronizedIfViewReverseTransformationFailed()
    {
        $transformer = $this->getDataTransformer();
        $transformer->expects($this->once())
            ->method('reverseTransform')
            ->will($this->throwException(new Symfony_Component_Form_Exception_TransformationFailedException()));

        $form = $this->getBuilder()
            ->addViewTransformer($transformer)
            ->getForm();

        $form->bind('foobar');

        $this->assertFalse($form->isSynchronized());
    }

    public function testNotSynchronizedIfModelReverseTransformationFailed()
    {
        $transformer = $this->getDataTransformer();
        $transformer->expects($this->once())
            ->method('reverseTransform')
            ->will($this->throwException(new Symfony_Component_Form_Exception_TransformationFailedException()));

        $form = $this->getBuilder()
            ->addModelTransformer($transformer)
            ->getForm();

        $form->bind('foobar');

        $this->assertFalse($form->isSynchronized());
    }

    public function testEmptyDataCreatedBeforeTransforming()
    {
        $form = $this->getBuilder()
            ->setEmptyData('foo')
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            // direction is reversed!
            'bar' => 'foo',
        )))
            ->getForm();

        $form->bind('');

        $this->assertEquals('bar', $form->getData());
    }

    public function testEmptyDataFromClosure()
    {
        $test = $this;
        $form = $this->getBuilder()
            ->setEmptyData(create_function('$form', '
            // the form instance is passed to the closure to allow use
            // of form data when creating the empty value
            PHPUnit_Framework_Assert::assertInstanceOf("Symfony_Component_Form_FormInterface", $form);

            return "foo";
        '))
            ->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            // direction is reversed!
            'bar' => 'foo',
        )))
            ->getForm();

        $form->bind('');

        $this->assertEquals('bar', $form->getData());
    }

    public function testBindValidatesAfterTransformation()
    {
        $test = $this;
        $validator = $this->getFormValidator();
        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handle'));
        $form = $this->getBuilder()
            ->addValidator($validator)
            ->getForm();

        $validator->expects($this->once())
            ->method('validate')
            ->with($form)
            ->will($this->returnCallback(create_function('$form', '
            PHPUnit_Framework_Assert::assertEquals("foobar", $form->getData());
        ')));

        $form->bind('foobar');

        restore_error_handler();
    }

    public function testBindResetsErrors()
    {
        $this->form->addError(new Symfony_Component_Form_FormError('Error!'));
        $this->form->bind('foobar');

        $this->assertSame(array(), $this->form->getErrors());
    }

    public function testCreateView()
    {
        $type = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');
        $view = $this->getMock('Symfony_Component_Form_FormView');
        $form = $this->getBuilder()->setType($type)->getForm();

        $type->expects($this->once())
            ->method('createView')
            ->with($form)
            ->will($this->returnValue($view));

        $this->assertSame($view, $form->createView());
    }

    public function testCreateViewWithParent()
    {
        $type = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');
        $view = $this->getMock('Symfony_Component_Form_FormView');
        $parentForm = $this->getMock('Symfony_Component_Form_Test_FormInterface');
        $parentView = $this->getMock('Symfony_Component_Form_FormView');
        $form = $this->getBuilder()->setType($type)->getForm();
        $form->setParent($parentForm);

        $parentForm->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($parentView));

        $type->expects($this->once())
            ->method('createView')
            ->with($form, $parentView)
            ->will($this->returnValue($view));

        $this->assertSame($view, $form->createView());
    }

    public function testCreateViewWithExplicitParent()
    {
        $type = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');
        $view = $this->getMock('Symfony_Component_Form_FormView');
        $parentView = $this->getMock('Symfony_Component_Form_FormView');
        $form = $this->getBuilder()->setType($type)->getForm();

        $type->expects($this->once())
            ->method('createView')
            ->with($form, $parentView)
            ->will($this->returnValue($view));

        $this->assertSame($view, $form->createView($parentView));
    }

    public function testGetErrorsAsString()
    {
        $this->form->addError(new Symfony_Component_Form_FormError('Error!'));

        $this->assertEquals("ERROR: Error!\n", $this->form->getErrorsAsString());
    }

    public function testFormCanHaveEmptyName()
    {
        $form = $this->getBuilder('')->getForm();

        $this->assertEquals('', $form->getName());
    }

    public function testSetNullParentWorksWithEmptyName()
    {
        $form = $this->getBuilder('')->getForm();
        $form->setParent(null);

        $this->assertNull($form->getParent());
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     * @expectedExceptionMessage A form with an empty name cannot have a parent form.
     */
    public function testFormCannotHaveEmptyNameNotInRootLevel()
    {
        $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->add($this->getBuilder(''))
            ->getForm();
    }

    public function testGetPropertyPathReturnsConfiguredPath()
    {
        $form = $this->getBuilder()->setPropertyPath('address.street')->getForm();

        $this->assertEquals(new Symfony_Component_PropertyAccess_PropertyPath('address.street'), $form->getPropertyPath());
    }

    // see https://github.com/symfony/symfony/issues/3903
    public function testGetPropertyPathDefaultsToNameIfParentHasDataClass()
    {
        $parent = $this->getBuilder(null, null, 'stdClass')
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $form = $this->getBuilder('name')->getForm();
        $parent->add($form);

        $this->assertEquals(new Symfony_Component_PropertyAccess_PropertyPath('name'), $form->getPropertyPath());
    }

    // see https://github.com/symfony/symfony/issues/3903
    public function testGetPropertyPathDefaultsToIndexedNameIfParentDataClassIsNull()
    {
        $parent = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->getForm();
        $form = $this->getBuilder('name')->getForm();
        $parent->add($form);

        $this->assertEquals(new Symfony_Component_PropertyAccess_PropertyPath('[name]'), $form->getPropertyPath());
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     */
    public function testViewDataMustNotBeObjectIfDataClassIsNull()
    {
        $config = new Symfony_Component_Form_FormConfigBuilder('name', null, $this->dispatcher);
        $config->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'foo' => new stdClass(),
        )));
        $form = new Symfony_Component_Form_Form($config);

        $form->setData('foo');
    }

    public function testViewDataMayBeArrayAccessIfDataClassIsNull()
    {
        $arrayAccess = $this->getMock('ArrayAccess');
        $config = new Symfony_Component_Form_FormConfigBuilder('name', null, $this->dispatcher);
        $config->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'foo' => $arrayAccess,
        )));
        $form = new Symfony_Component_Form_Form($config);

        $form->setData('foo');

        $this->assertSame($arrayAccess, $form->getViewData());
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     */
    public function testViewDataMustBeObjectIfDataClassIsSet()
    {
        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->addViewTransformer(new Symfony_Component_Form_Tests_Fixtures_FixedDataTransformer(array(
            '' => '',
            'foo' => array('bar' => 'baz'),
        )));
        $form = new Symfony_Component_Form_Form($config);

        $form->setData('foo');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     */
    public function testSetDataCannotInvokeItself()
    {
        // Cycle detection to prevent endless loops
        $config = new Symfony_Component_Form_FormConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->addEventListener(Symfony_Component_Form_FormEvents::PRE_SET_DATA, create_function('Symfony_Component_Form_FormEvent $event', '
            $event->getForm()->setData("bar");
        '));
        $form = new Symfony_Component_Form_Form($config);

        $form->setData('foo');
    }

    public function testBindingWrongDataIsIgnored()
    {
        $test = $this;

        $child = $this->getBuilder('child', $this->dispatcher);
        $child->addEventListener(Symfony_Component_Form_FormEvents::PRE_BIND, create_function('Symfony_Component_Form_FormEvent $event', '
            // child form doesn\'t receive the wrong data that is bound on parent
            PHPUnit_Framework_Assert::assertNull($event->getData());
        '));

        $parent = $this->getBuilder('parent', new Symfony_Component_EventDispatcher_EventDispatcher())
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->add($child)
            ->getForm();

        $parent->bind('not-an-array');
    }

    protected function createForm()
    {
        return $this->getBuilder()->getForm();
    }
}
