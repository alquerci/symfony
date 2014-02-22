<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Locale_Tests_Stub_StubLocaleTest extends Symfony_Component_Locale_Tests_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetDisplayCountriesWithUnsupportedLocale()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayCountries('pt_BR');
    }

    public function testGetDisplayCountries()
    {
        $countries = Symfony_Component_Locale_Stub_StubLocale::getDisplayCountries('en');
        $this->assertEquals('Brazil', $countries['BR']);
    }

    public function testGetCountries()
    {
        $countries = Symfony_Component_Locale_Stub_StubLocale::getCountries();
        $this->assertTrue(in_array('BR', $countries));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetDisplayLanguagesWithUnsupportedLocale()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayLanguages('pt_BR');
    }

    public function testGetDisplayLanguages()
    {
        $languages = Symfony_Component_Locale_Stub_StubLocale::getDisplayLanguages('en');
        $this->assertEquals('Brazilian Portuguese', $languages['pt_BR']);
    }

    public function testGetLanguages()
    {
        $languages = Symfony_Component_Locale_Stub_StubLocale::getLanguages();
        $this->assertTrue(in_array('pt_BR', $languages));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetCurrenciesDataWithUnsupportedLocale()
    {
        Symfony_Component_Locale_Stub_StubLocale::getCurrenciesData('pt_BR');
    }

    public function testGetCurrenciesData()
    {
        if ($this->isIntlExtensionLoaded()) {
            $symbol = $this->isSameAsIcuVersion('4.8') ? 'BR$' : 'R$';
        } else {
            $symbol = 'R$';
        }

        $currencies = Symfony_Component_Locale_Stub_StubLocale::getCurrenciesData('en');
        $this->assertEquals($symbol, $currencies['BRL']['symbol']);
        $this->assertEquals('Brazilian Real', $currencies['BRL']['name']);
        $this->assertEquals(2, $currencies['BRL']['fractionDigits']);
        $this->assertEquals(0, $currencies['BRL']['roundingIncrement']);
    }

    public function testGetDisplayCurrencies()
    {
        $currencies = Symfony_Component_Locale_Stub_StubLocale::getDisplayCurrencies('en');
        $this->assertEquals('Brazilian Real', $currencies['BRL']);

        // Checking that the cache is being used
        $currencies = Symfony_Component_Locale_Stub_StubLocale::getDisplayCurrencies('en');
        $this->assertEquals('Argentine Peso', $currencies['ARS']);
    }

    public function testGetCurrencies()
    {
        $currencies = Symfony_Component_Locale_Stub_StubLocale::getCurrencies();
        $this->assertTrue(in_array('BRL', $currencies));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetDisplayLocalesWithUnsupportedLocale()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayLocales('pt');
    }

    public function testGetDisplayLocales()
    {
        $locales = Symfony_Component_Locale_Stub_StubLocale::getDisplayLocales('en');
        $this->assertEquals('Portuguese', $locales['pt']);
    }

    public function testGetLocales()
    {
        $locales = Symfony_Component_Locale_Stub_StubLocale::getLocales();
        $this->assertTrue(in_array('pt', $locales));
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testAcceptFromHttp()
    {
        Symfony_Component_Locale_Stub_StubLocale::acceptFromHttp('pt-br,en-us;q=0.7,en;q=0.5');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testComposeLocale()
    {
        $subtags = array(
            'language' => 'pt',
            'script'   => 'Latn',
            'region'   => 'BR'
        );
        Symfony_Component_Locale_Stub_StubLocale::composeLocale($subtags);
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testFilterMatches()
    {
        Symfony_Component_Locale_Stub_StubLocale::filterMatches('pt-BR', 'pt-BR');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetAllVariants()
    {
        Symfony_Component_Locale_Stub_StubLocale::getAllVariants('pt_BR_Latn');
    }

    public function testGetDefault()
    {
        $this->assertEquals('en', Symfony_Component_Locale_Stub_StubLocale::getDefault());
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetDisplayLanguage()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayLanguage('pt-Latn-BR', 'en');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetDisplayName()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayName('pt-Latn-BR', 'en');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetDisplayRegion()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayRegion('pt-Latn-BR', 'en');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetDisplayScript()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayScript('pt-Latn-BR', 'en');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetDisplayVariant()
    {
        Symfony_Component_Locale_Stub_StubLocale::getDisplayVariant('pt-Latn-BR', 'en');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetKeywords()
    {
        Symfony_Component_Locale_Stub_StubLocale::getKeywords('pt-BR@currency=BRL');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetPrimaryLanguage()
    {
        Symfony_Component_Locale_Stub_StubLocale::getPrimaryLanguage('pt-Latn-BR');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetRegion()
    {
        Symfony_Component_Locale_Stub_StubLocale::getRegion('pt-Latn-BR');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetScript()
    {
        Symfony_Component_Locale_Stub_StubLocale::getScript('pt-Latn-BR');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testLookup()
    {
        $langtag = array(
            'pt-Latn-BR',
            'pt-BR'
        );
        Symfony_Component_Locale_Stub_StubLocale::lookup($langtag, 'pt-BR-x-priv1');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testParseLocale()
    {
        Symfony_Component_Locale_Stub_StubLocale::parseLocale('pt-Latn-BR');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testSetDefault()
    {
        Symfony_Component_Locale_Stub_StubLocale::setDefault('pt_BR');
    }

    public function testSetDefaultAcceptsEn()
    {
        Symfony_Component_Locale_Stub_StubLocale::setDefault('en');
    }
}
