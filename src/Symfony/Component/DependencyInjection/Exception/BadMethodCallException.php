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
 * Base BadMethodCallException for Dependency Injection component.
 */
class Symfony_Component_DependencyInjection_Exception_BadMethodCallException extends BadMethodCallException implements Symfony_Component_DependencyInjection_Exception_ExceptionInterface
{
}
