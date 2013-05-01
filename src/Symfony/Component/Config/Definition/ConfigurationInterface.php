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
 * Configuration interface
 *
 * @author Victor Berchet <victor@suumit.com>
 */
interface Symfony_Component_Config_Definition_ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return Symfony_Component_Config_Definition_Builder_TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder();
}
