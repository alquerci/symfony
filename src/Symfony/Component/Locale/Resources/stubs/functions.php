<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stub implementation for the intl_is_failure function of the intl extension
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @param  integer $errorCode  The error code returned by intl_get_error_code()
 * @return Boolean Whether the error code indicates an error
 * @see    Symfony_Component_Locale_Stub_StubIntl::isFailure
 */
function intl_is_failure($errorCode)
{
    return Symfony_Component_Locale_Stub_StubIntl::isFailure($errorCode);
}

/**
 * Stub implementation for the intl_get_error_code function of the intl extension
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @return Boolean The error code of the last intl function call or
 *                 Symfony_Component_Locale_Stub_StubIntl::U_ZERO_ERROR if no error occurred
 * @see    Symfony_Component_Locale_Stub_StubIntl::getErrorCode
 */
function intl_get_error_code()
{
    return Symfony_Component_Locale_Stub_StubIntl::getErrorCode();
}

/**
 * Stub implementation for the intl_get_error_code function of the intl extension
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @return Boolean The error message of the last intl function call or
 *                 "U_ZERO_ERROR" if no error occurred
 * @see    Symfony_Component_Locale_Stub_StubIntl::getErrorMessage
 */
function intl_get_error_message()
{
    return Symfony_Component_Locale_Stub_StubIntl::getErrorMessage();
}

/**
 * Stub implementation for the intl_error_name function of the intl extension
 *
 * @param integer $errorCode
 *
 * @return String will be the same as the name of the error code constant
 *
 * @see    Symfony_Component_Locale_Stub_StubIntl::getErrorName
 */
function intl_error_name($errorCode)
{
    return Symfony_Component_Locale_Stub_StubIntl::getErrorName($errorCode);
}
