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
 * Scope Interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @api
 */
interface Symfony_Component_DependencyInjection_ScopeInterface
{
    /**
     * @api
     */
    public function getName();

    /**
     * @api
     */
    public function getParentName();
}
