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
 * DumperInterface is the interface implemented by service container dumper classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_DependencyInjection_Dumper_DumperInterface
{
    /**
     * Dumps the service container.
     *
     * @param array $options An array of options
     *
     * @return string The representation of the service container
     *
     * @api
     */
    public function dump(array $options = array());
}
