<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_ConstraintTest extends PHPUnit_Framework_TestCase
{
    public function testSetProperties()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array(
            'property1' => 'foo',
            'property2' => 'bar',
        ));

        $this->assertEquals('foo', $constraint->property1);
        $this->assertEquals('bar', $constraint->property2);
    }

    public function testSetNotExistingPropertyThrowsException()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_InvalidOptionsException');

        new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array(
            'foo' => 'bar',
        ));
    }

    public function testMagicPropertiesAreNotAllowed()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA();

        $this->setExpectedException('Symfony_Component_Validator_Exception_InvalidOptionsException');

        $constraint->foo = 'bar';
    }

    public function testInvalidAndRequiredOptionsPassed()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_InvalidOptionsException');

        new Symfony_Component_Validator_Tests_Fixtures_ConstraintC(array(
            'option1' => 'default',
            'foo' => 'bar'
        ));
    }

    public function testSetDefaultProperty()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA('foo');

        $this->assertEquals('foo', $constraint->property2);
    }

    public function testSetDefaultPropertyDoctrineStyle()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('value' => 'foo'));

        $this->assertEquals('foo', $constraint->property2);
    }

    public function testSetUndefinedDefaultProperty()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_ConstraintDefinitionException');

        new Symfony_Component_Validator_Tests_Fixtures_ConstraintB('foo');
    }

    public function testRequiredOptionsMustBeDefined()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_MissingOptionsException');

        new Symfony_Component_Validator_Tests_Fixtures_ConstraintC();
    }

    public function testRequiredOptionsPassed()
    {
        new Symfony_Component_Validator_Tests_Fixtures_ConstraintC(array('option1' => 'default'));
    }

    public function testGroupsAreConvertedToArray()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => 'Foo'));

        $this->assertEquals(array('Foo'), $constraint->groups);
    }

    public function testAddDefaultGroupAddsGroup()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => 'Default'));
        $constraint->addImplicitGroupName('Foo');
        $this->assertEquals(array('Default', 'Foo'), $constraint->groups);
    }

    public function testAllowsSettingZeroRequiredPropertyValue()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(0);
        $this->assertEquals(0, $constraint->property2);
    }

    public function testCanCreateConstraintWithNoDefaultOptionAndEmptyArray()
    {
        new Symfony_Component_Validator_Tests_Fixtures_ConstraintB(array());
    }

    public function testGetTargetsCanBeString()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ClassConstraint;

        $this->assertEquals('class', $constraint->getTargets());
    }

    public function testGetTargetsCanBeArray()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA;

        $this->assertEquals(array('property', 'class'), $constraint->getTargets());
    }
}
