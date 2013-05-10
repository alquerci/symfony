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
 * This exception is thrown whenever the key of an array is not unique. This can
 * only be the case if the configuration is coming from an XML file.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Config_Definition_Exception_DuplicateKeyException extends Symfony_Component_Config_Definition_Exception_InvalidConfigurationException
{
}
