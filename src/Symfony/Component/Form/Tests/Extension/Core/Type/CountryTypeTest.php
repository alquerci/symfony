<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_Type_CountryTypeTest extends Symfony_Component_Form_Tests_Extension_Core_Type_LocalizedTestCase
{
    public function testCountriesAreSelectable()
    {
        Locale::setDefault('en');

        $form = $this->factory->create('country');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        // Don't check objects for identity
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('DE', 'DE', 'Germany'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('GB', 'GB', 'United Kingdom'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('US', 'US', 'United States'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('FR', 'FR', 'France'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('MY', 'MY', 'Malaysia'), $choices, '', false, false);
    }

    public function testUnknownCountryIsNotIncluded()
    {
        $form = $this->factory->create('country', 'country');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        foreach ($choices as $choice) {
            if ('ZZ' === $choice->value) {
                $this->fail('Should not contain choice "ZZ"');
            }
        }
    }
}
