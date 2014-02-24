<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_AllValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_AllValidator();
        $this->validator->initialize($this->context);

        $this->context->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue('MyGroup'));
    }

    protected function tearDown()
    {
        $this->validator = null;
        $this->context = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_All(new Symfony_Component_Validator_Constraints_Range(array('min' => 4))));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testThrowsExceptionIfNotTraversable()
    {
        $this->validator->validate('foo.barbar', new Symfony_Component_Validator_Constraints_All(new Symfony_Component_Validator_Constraints_Range(array('min' => 4))));
    }

    /**
     * @dataProvider getValidArguments
     */
    public function testWalkSingleConstraint($array)
    {
        $constraint = new Symfony_Component_Validator_Constraints_Range(array('min' => 4));

        $i = 1;

        foreach ($array as $key => $value) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint, '['.$key.']', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($array, new Symfony_Component_Validator_Constraints_All($constraint));
    }

    /**
     * @dataProvider getValidArguments
     */
    public function testWalkMultipleConstraints($array)
    {
        $constraint1 = new Symfony_Component_Validator_Constraints_Range(array('min' => 4));
        $constraint2 = new Symfony_Component_Validator_Constraints_NotNull();

        $constraints = array($constraint1, $constraint2);
        $i = 1;

        foreach ($array as $key => $value) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint1, '['.$key.']', 'MyGroup');
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint2, '['.$key.']', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($array, new Symfony_Component_Validator_Constraints_All($constraints));
    }

    public function getValidArguments()
    {
        return array(
            array(array(5, 6, 7)),
            array(new ArrayObject(array(5, 6, 7))),
        );
    }
}
