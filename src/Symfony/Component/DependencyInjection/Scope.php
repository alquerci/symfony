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
 * Scope class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @api
 */
class Symfony_Component_DependencyInjection_Scope implements Symfony_Component_DependencyInjection_ScopeInterface
{
    private $name;
    private $parentName;

    /**
     * @api
     */
    public function __construct($name, $parentName = Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_CONTAINER)
    {
        $this->name = $name;
        $this->parentName = $parentName;
    }

    /**
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @api
     */
    public function getParentName()
    {
        return $this->parentName;
    }
}
