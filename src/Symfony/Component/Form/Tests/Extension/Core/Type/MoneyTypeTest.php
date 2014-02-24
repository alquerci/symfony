<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_Type_MoneyTypeTest extends Symfony_Component_Form_Tests_Extension_Core_Type_LocalizedTestCase
{
    public function testPassMoneyPatternToView()
    {
        Locale::setDefault('en');

        $form = $this->factory->create('money');
        $view = $form->createView();

        $this->assertSame('€ {{ widget }}', $view->vars['money_pattern']);
    }

    public function testMoneyPatternWorksForYen()
    {
        Locale::setDefault('en');

        $form = $this->factory->create('money', null, array('currency' => 'JPY'));
        $view = $form->createView();
        $this->assertTrue((Boolean) strstr($view->vars['money_pattern'], '¥'));
    }

    // https://github.com/symfony/symfony/issues/5458
    public function testPassDifferentPatternsForDifferentCurrencies()
    {
        Locale::setDefault('en');

        $form1 = $this->factory->create('money', null, array('currency' => 'GBP'));
        $form2 = $this->factory->create('money', null, array('currency' => 'EUR'));
        $view1 = $form1->createView();
        $view2 = $form2->createView();

        $this->assertSame('£ {{ widget }}', $view1->vars['money_pattern']);
        $this->assertSame('€ {{ widget }}', $view2->vars['money_pattern']);
    }
}
