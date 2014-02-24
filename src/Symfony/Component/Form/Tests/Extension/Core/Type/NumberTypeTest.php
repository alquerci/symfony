<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_Type_NumberTypeTest extends Symfony_Component_Form_Tests_Extension_Core_Type_LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Locale::setDefault('de_DE');
    }

    public function testDefaultFormatting()
    {
        $form = $this->factory->create('number');
        $form->setData('12345.67890');
        $view = $form->createView();

        $this->assertSame('12345,679', $view->vars['value']);
    }

    public function testDefaultFormattingWithGrouping()
    {
        $form = $this->factory->create('number', null, array('grouping' => true));
        $form->setData('12345.67890');
        $view = $form->createView();

        $this->assertSame('12.345,679', $view->vars['value']);
    }

    public function testDefaultFormattingWithPrecision()
    {
        $form = $this->factory->create('number', null, array('precision' => 2));
        $form->setData('12345.67890');
        $view = $form->createView();

        $this->assertSame('12345,68', $view->vars['value']);
    }

    public function testDefaultFormattingWithRounding()
    {
        $form = $this->factory->create('number', null, array('precision' => 0, 'rounding_mode' => NumberFormatter::ROUND_UP));
        $form->setData('12345.54321');
        $view = $form->createView();

        $this->assertSame('12346', $view->vars['value']);
    }
}
