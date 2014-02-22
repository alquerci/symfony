<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Validator_ConstraintValidatorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetInstanceCreatesValidator()
    {
        $class = get_class($this->getMockForAbstractClass('Symfony_Component_Validator_ConstraintValidator'));

        $constraint = $this->getMock('Symfony_Component_Validator_Constraint');
        $constraint
            ->expects($this->once())
            ->method('validatedBy')
            ->will($this->returnValue($class));

        $factory = new Symfony_Bundle_FrameworkBundle_Validator_ConstraintValidatorFactory(new Symfony_Component_DependencyInjection_Container());
        $this->assertInstanceOf($class, $factory->getInstance($constraint));
    }

    public function testGetInstanceReturnsExistingValidator()
    {
        $factory = new Symfony_Bundle_FrameworkBundle_Validator_ConstraintValidatorFactory(new Symfony_Component_DependencyInjection_Container());
        $v1 = $factory->getInstance(new Symfony_Component_Validator_Constraints_Blank());
        $v2 = $factory->getInstance(new Symfony_Component_Validator_Constraints_Blank());
        $this->assertSame($v1, $v2);
    }

    public function testGetInstanceReturnsService()
    {
        $service = 'validator_constraint_service';
        $alias = 'validator_constraint_alias';
        $validator = new stdClass();

        // mock ContainerBuilder b/c it implements TaggedContainerInterface
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $container
            ->expects($this->once())
            ->method('get')
            ->with($service)
            ->will($this->returnValue($validator));

        $constraint = $this->getMock('Symfony_Component_Validator_Constraint');
        $constraint
            ->expects($this->once())
            ->method('validatedBy')
            ->will($this->returnValue($alias));

        $factory = new Symfony_Bundle_FrameworkBundle_Validator_ConstraintValidatorFactory($container, array('validator_constraint_alias' => 'validator_constraint_service'));
        $this->assertSame($validator, $factory->getInstance($constraint));
    }
}
