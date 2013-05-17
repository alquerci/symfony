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
 * RequestHelper provides access to the current request parameters.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Helper_RequestHelper extends Symfony_Component_Templating_Helper_Helper
{
    protected $request;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     */
    public function __construct(Symfony_Component_HttpFoundation_Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns a parameter from the current request object.
     *
     * @param string $key     The name of the parameter
     * @param string $default A default value
     *
     * @return mixed
     *
     * @see Symfony_Component_HttpFoundation_Request::get()
     */
    public function getParameter($key, $default = null)
    {
        return $this->request->get($key, $default);
    }

    /**
     * Returns the locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->request->getLocale();
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'request';
    }
}
