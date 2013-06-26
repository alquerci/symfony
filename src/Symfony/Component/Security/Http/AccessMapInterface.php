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
 * AccessMap allows configuration of different access control rules for
 * specific parts of the website.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kris Wallsmith <kris@symfony.com>
 */
interface Symfony_Component_Security_Http_AccessMapInterface
{
    /**
     * Returns security attributes and required channel for the supplied request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request The current request
     *
     * @return array A tuple of security attributes and the required channel
     */
    public function getPatterns(Symfony_Component_HttpFoundation_Request $request);
}
