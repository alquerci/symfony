<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Locale_Tests_LocaleTest extends Symfony_Component_Locale_Tests_TestCase
{
    public function testGetDisplayCountriesReturnsFullListForSubLocale()
    {
        $this->skipIfIntlExtensionIsNotLoaded();

        Symfony_Component_Locale_Locale::setDefault('de_CH');

        $countriesDe = Symfony_Component_Locale_Locale::getDisplayCountries('de');
        $countriesDeCh = Symfony_Component_Locale_Locale::getDisplayCountries('de_CH');

        $this->assertEquals(count($countriesDe), count($countriesDeCh));
        $this->assertEquals($countriesDe['BD'], 'Bangladesch');
        $this->assertEquals($countriesDeCh['BD'], 'Bangladesh');
    }

    public function testGetDisplayLanguagesReturnsFullListForSubLocale()
    {
        $this->skipIfIntlExtensionIsNotLoaded();

        Symfony_Component_Locale_Locale::setDefault('de_CH');

        $languagesDe = Symfony_Component_Locale_Locale::getDisplayLanguages('de');
        $languagesDeCh = Symfony_Component_Locale_Locale::getDisplayLanguages('de_CH');

        $this->assertEquals(count($languagesDe), count($languagesDeCh));
        $this->assertEquals($languagesDe['be'], 'Weißrussisch');
        $this->assertEquals($languagesDeCh['be'], 'Weissrussisch');
    }

    public function testGetDisplayLocalesReturnsFullListForSubLocale()
    {
        $this->skipIfIntlExtensionIsNotLoaded();

        Symfony_Component_Locale_Locale::setDefault('de_CH');

        $localesDe = Symfony_Component_Locale_Locale::getDisplayLocales('de');
        $localesDeCh = Symfony_Component_Locale_Locale::getDisplayLocales('de_CH');

        $this->assertEquals(count($localesDe), count($localesDeCh));
        $this->assertEquals($localesDe['be'], 'Weißrussisch');
        $this->assertEquals($localesDeCh['be'], 'Weissrussisch');
    }
}
