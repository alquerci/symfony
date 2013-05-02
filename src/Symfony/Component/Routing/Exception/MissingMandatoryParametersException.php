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
 * Exception thrown when a route cannot be generated because of missing
 * mandatory parameters.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * @api
 */
class Symfony_Component_Routing_Exception_MissingMandatoryParametersException extends InvalidArgumentException implements Symfony_Component_Routing_Exception_ExceptionInterface
{
}
