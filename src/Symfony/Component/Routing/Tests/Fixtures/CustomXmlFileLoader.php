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
 * Symfony_Component_Routing_Loader_XmlFileLoader with schema validation turned off
 */
class Symfony_Component_Routing_Tests_Fixtures_CustomXmlFileLoader extends Symfony_Component_Routing_Loader_XmlFileLoader
{
    protected function loadFile($file)
    {
        return Symfony_Component_Config_Util_XmlUtils::loadFile($file, create_function('', 'return true;'));
    }
}
