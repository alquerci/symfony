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
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Routing_Tests_Fixtures_RedirectableUrlMatcher extends Symfony_Component_Routing_Matcher_UrlMatcher implements Symfony_Component_Routing_Matcher_RedirectableUrlMatcherInterface
{
    public function redirect($path, $route, $scheme = null)
    {
        return array(
            '_controller' => 'Some controller reference...',
            'path'        => $path,
            'scheme'      => $scheme,
        );
    }
}
