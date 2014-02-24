<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Form_Tests_Extension_Core_Type_TypeTestCase extends Symfony_Component_Form_Tests_FormIntegrationTestCase
{
    /**
     * @var Symfony_Component_Form_FormBuilder
     */
    protected $builder;

    /**
     * @var Symfony_Component_EventDispatcher_EventDispatcher
     */
    protected $dispatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->builder = new Symfony_Component_Form_FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    public static function assertDateTimeEquals(DateTime $expected, DateTime $actual)
    {
        self::assertEquals($expected->format('c'), $actual->format('c'));
    }
}
