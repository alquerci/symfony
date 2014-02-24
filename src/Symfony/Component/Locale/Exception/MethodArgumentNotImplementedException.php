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
 * @author Eriksen Costa <eriksen.costa@infranology.com.br>
 */
class Symfony_Component_Locale_Exception_MethodArgumentNotImplementedException extends Symfony_Component_Locale_Exception_NotImplementedException
{
    /**
     * Constructor
     *
     * @param string $methodName The method name that raised the exception
     * @param string $argName    The argument name that is not implemented
     */
    public function __construct($methodName, $argName)
    {
        $message = sprintf('The %s() method\'s argument $%s behavior is not implemented.', $methodName, $argName);
        parent::__construct($message);
    }
}
