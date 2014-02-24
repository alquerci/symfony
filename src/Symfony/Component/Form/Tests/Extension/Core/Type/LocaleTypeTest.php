<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_Type_LocaleTypeTest extends Symfony_Component_Form_Tests_Extension_Core_Type_LocalizedTestCase
{
    public function testLocalesAreSelectable()
    {
        Locale::setDefault('de_AT');

        $form = $this->factory->create('locale');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en', 'en', 'Englisch'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en_GB', 'en_GB', 'Englisch (Vereinigtes Königreich)'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('zh_Hant_MO', 'zh_Hant_MO', 'Chinesisch (traditionell, Sonderverwaltungszone Macao)'), $choices, '', false, false);
    }
}
