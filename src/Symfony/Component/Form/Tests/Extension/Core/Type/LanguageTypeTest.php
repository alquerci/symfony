<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_Type_LanguageTypeTest extends Symfony_Component_Form_Tests_Extension_Core_Type_LocalizedTestCase
{
    public function testCountriesAreSelectable()
    {
        Locale::setDefault('en');

        $form = $this->factory->create('language');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en', 'en', 'English'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en_GB', 'en_GB', 'British English'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en_US', 'en_US', 'U.S. English'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('fr', 'fr', 'French'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('my', 'my', 'Burmese'), $choices, '', false, false);
    }

    public function testMultipleLanguagesIsNotIncluded()
    {
        $form = $this->factory->create('language', 'language');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        $this->assertNotContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('mul', 'mul', 'Multiple Languages'), $choices, '', false, false);
    }
}
