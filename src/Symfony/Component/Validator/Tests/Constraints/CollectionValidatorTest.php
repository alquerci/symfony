<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Validator_Tests_Constraints_CollectionValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_CollectionValidator();
        $this->validator->initialize($this->context);

        $this->context->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue('MyGroup'));
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    abstract protected function prepareTestData(array $contents);

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Collection(array('fields' => array(
            'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
        ))));
    }

    public function testFieldsAsDefaultOption()
    {
        $data = $this->prepareTestData(array('foo' => 'foobar'));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
        )));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testThrowsExceptionIfNotTraversable()
    {
        $this->validator->validate('foobar', new Symfony_Component_Validator_Constraints_Collection(array('fields' => array(
            'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
        ))));
    }

    public function testWalkSingleConstraint()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Range(array('min' => 4));

        $array = array(
            'foo' => 3,
            'bar' => 5,
        );
        $i = 1;

        foreach ($array as $key => $value) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint, '['.$key.']', 'MyGroup');
        }

        $data = $this->prepareTestData($array);

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => $constraint,
                'bar' => $constraint,
            ),
        )));
    }

    public function testWalkMultipleConstraints()
    {
        $constraints = array(
            new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
            new Symfony_Component_Validator_Constraints_NotNull(),
        );

        $array = array(
            'foo' => 3,
            'bar' => 5,
        );
        $i = 1;

        foreach ($array as $key => $value) {
            foreach ($constraints as $constraint) {
                $this->context->expects($this->at($i++))
                    ->method('validateValue')
                    ->with($value, $constraint, '['.$key.']', 'MyGroup');
            }
        }

        $data = $this->prepareTestData($array);

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => $constraints,
                'bar' => $constraints,
            )
        )));
    }

    public function testExtraFieldsDisallowed()
    {
        $data = $this->prepareTestData(array(
            'foo' => 5,
            'baz' => 6,
        ));

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('[baz]', 'myMessage', array(
                '{{ field }}' => 'baz'
            ));

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
            ),
            'extraFieldsMessage' => 'myMessage',
        )));
    }

    // bug fix
    public function testNullNotConsideredExtraField()
    {
        $data = $this->prepareTestData(array(
            'foo' => null,
        ));

        $constraint = new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
            ),
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, $constraint);
    }

    public function testExtraFieldsAllowed()
    {
        $data = $this->prepareTestData(array(
            'foo' => 5,
            'bar' => 6,
        ));

        $constraint = new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
            ),
            'allowExtraFields' => true,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, $constraint);
    }

    public function testMissingFieldsDisallowed()
    {
        $data = $this->prepareTestData(array());

        $constraint = new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
            ),
            'missingFieldsMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('[foo]', 'myMessage', array(
                '{{ field }}' => 'foo',
            ));

        $this->validator->validate($data, $constraint);
    }

    public function testMissingFieldsAllowed()
    {
        $data = $this->prepareTestData(array());

        $constraint = new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
            ),
            'allowMissingFields' => true,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, $constraint);
    }

    public function testOptionalFieldPresent()
    {
        $data = $this->prepareTestData(array(
            'foo' => null,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Optional(),
        )));
    }

    public function testOptionalFieldNotPresent()
    {
        $data = $this->prepareTestData(array());

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Optional(),
        )));
    }

    public function testOptionalFieldSingleConstraint()
    {
        $array = array(
            'foo' => 5,
        );

        $constraint = new Symfony_Component_Validator_Constraints_Range(array('min' => 4));

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($array['foo'], $constraint, '[foo]', 'MyGroup');

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Optional($constraint),
        )));
    }

    public function testOptionalFieldMultipleConstraints()
    {
        $array = array(
            'foo' => 5,
        );

        $constraints = array(
            new Symfony_Component_Validator_Constraints_NotNull(),
            new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
        );
        $i = 1;

        foreach ($constraints as $constraint) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($array['foo'], $constraint, '[foo]', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Optional($constraints),
        )));
    }

    public function testRequiredFieldPresent()
    {
        $data = $this->prepareTestData(array(
            'foo' => null,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Required(),
        )));
    }

    public function testRequiredFieldNotPresent()
    {
        $data = $this->prepareTestData(array());

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('[foo]', 'myMessage', array(
                '{{ field }}' => 'foo',
            ));

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Collection_Required(),
            ),
            'missingFieldsMessage' => 'myMessage',
        )));
    }

    public function testRequiredFieldSingleConstraint()
    {
        $array = array(
            'foo' => 5,
        );

        $constraint = new Symfony_Component_Validator_Constraints_Range(array('min' => 4));

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($array['foo'], $constraint, '[foo]', 'MyGroup');

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($data, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Required($constraint),
        )));
    }

    public function testRequiredFieldMultipleConstraints()
    {
        $array = array(
            'foo' => 5,
        );

        $constraints = array(
            new Symfony_Component_Validator_Constraints_NotNull(),
            new Symfony_Component_Validator_Constraints_Range(array('min' => 4)),
        );
        $i = 1;

        foreach ($constraints as $constraint) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($array['foo'], $constraint, '[foo]', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($array, new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Required($constraints),
        )));
    }

    public function testObjectShouldBeLeftUnchanged()
    {
        $value = new ArrayObject(array(
            'foo' => 3
        ));

        $this->validator->validate($value, new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => array(
                'foo' => new Symfony_Component_Validator_Constraints_Range(array('min' => 2)),
            )
        )));

        $this->assertEquals(array(
            'foo' => 3
        ), (array) $value);
    }
}
