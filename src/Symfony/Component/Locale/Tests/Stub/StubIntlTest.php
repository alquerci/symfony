<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__).'/../TestCase.php';

class Symfony_Component_Locale_Tests_Stub_StubIntlTest extends Symfony_Component_Locale_Tests_TestCase
{
    public function codeProvider()
    {
        return array (
            array(-129, '[BOGUS UErrorCode]'),
            array(0, 'U_ZERO_ERROR'),
            array(1, 'U_ILLEGAL_ARGUMENT_ERROR'),
            array(9, 'U_PARSE_ERROR'),
            array(129, '[BOGUS UErrorCode]'),
        );
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetErrorName($code, $name)
    {
        $this->assertSame($name, Symfony_Component_Locale_Stub_StubIntl::getErrorName($code));
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetErrorNameWithIntl($code, $name)
    {
        $this->skipIfIntlExtensionIsNotLoaded();
        $this->assertSame(intl_error_name($code), Symfony_Component_Locale_Stub_StubIntl::getErrorName($code));
    }
}
