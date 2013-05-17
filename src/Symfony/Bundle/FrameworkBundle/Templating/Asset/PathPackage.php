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
 * The path packages adds a version and a base path to asset URLs.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Asset_PathPackage extends Symfony_Component_Templating_Asset_PathPackage
{
    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpFoundation_Request $request The current request
     * @param string  $version The version
     * @param string  $format  The version format
     */
    public function __construct(Symfony_Component_HttpFoundation_Request $request, $version = null, $format = null)
    {
        parent::__construct($request->getBasePath(), $version, $format);
    }
}
