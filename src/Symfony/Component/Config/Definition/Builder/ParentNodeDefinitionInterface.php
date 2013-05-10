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
 * An interface that must be implemented by nodes which can have children
 *
 * @author Victor Berchet <victor@suumit.com>
 */
interface Symfony_Component_Config_Definition_Builder_ParentNodeDefinitionInterface
{
    public function children();

    public function append(Symfony_Component_Config_Definition_Builder_NodeDefinition $node);

    public function setBuilder(Symfony_Component_Config_Definition_Builder_NodeBuilder $builder);
}
