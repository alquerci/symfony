<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder extends Symfony_Component_Config_Definition_Builder_NodeBuilder
{
    public function barNode($name)
    {
        return $this->node($name, 'bar');
    }

    protected function getNodeClass($type)
    {
        switch ($type) {
            case 'variable':
                return 'Symfony_Component_Config_Tests_Fixtures_Builder_VariableNodeDefinition';
            case 'bar':
                return 'Symfony_Component_Config_Tests_Fixtures_Builder_BarNodeDefinition';
            default:
                return parent::getNodeClass($type);
        }
    }
}
