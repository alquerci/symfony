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
        Locale::setDefault('en');

        $form = $this->factory->create('locale');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en', 'en', 'English'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('en_GB', 'en_GB', 'English (United Kingdom)'), $choices, '', false, false);
        $this->assertContains(new Symfony_Component_Form_Extension_Core_View_ChoiceView('zh_Hant_MO', 'zh_Hant_MO', 'Traditional Chinese (Macau SAR China)'), $choices, '', false, false);
    }
}
