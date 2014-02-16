<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('intl_get_error_code')) {
    require_once dirname(__FILE__).'/../Resources/stubs/functions.php';
}

spl_autoload_register(create_function('$class', '
    if (in_array(ltrim($class, "/"), array("Collator", "IntlDateFormatter", "Locale", "NumberFormatter"))) {
        require_once dirname(__FILE__)."/../Resources/stubs/".ltrim($class, "/").".php";
    }

    if (0 === strpos(ltrim($class, "/"), "Symfony_Component_Locale")) {
        if (file_exists($file = dirname(__FILE__)."/../".substr(str_replace("\x5C\x5C", "/", $class), strlen("Symfony_Component_Locale")).".php")) {
            require_once $file;
        }
    }
'));
