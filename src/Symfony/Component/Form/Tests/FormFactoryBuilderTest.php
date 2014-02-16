<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_FormFactoryBuilderTest extends PHPUnit_Framework_TestCase
{
    private $registry;
    private $guesser;
    private $type;

    protected function setUp()
    {
        $this->guesser = $this->getMock('Symfony_Component_Form_FormTypeGuesserInterface');
        $this->type = new Symfony_Component_Form_Tests_Fixtures_FooType;
    }

    public function testAddType()
    {
        $factoryBuilder = new Symfony_Component_Form_FormFactoryBuilder;
        $factoryBuilder->addType($this->type);

        $factory = $factoryBuilder->getFormFactory();
        $registry = $this->readAttribute($factory, 'registry');
        $extensions = $registry->getExtensions();

        $this->assertCount(1, $extensions);
        $this->assertTrue($extensions[0]->hasType($this->type->getName()));
        $this->assertNull($extensions[0]->getTypeGuesser());
    }

    public function testAddTypeGuesser()
    {
        $factoryBuilder = new Symfony_Component_Form_FormFactoryBuilder;
        $factoryBuilder->addTypeGuesser($this->guesser);

        $factory = $factoryBuilder->getFormFactory();
        $registry = $this->readAttribute($factory, 'registry');
        $extensions = $registry->getExtensions();

        $this->assertCount(1, $extensions);
        $this->assertNotNull($extensions[0]->getTypeGuesser());
    }
}
