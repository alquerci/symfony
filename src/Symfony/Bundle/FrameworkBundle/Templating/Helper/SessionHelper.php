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
 * SessionHelper provides read-only access to the session attributes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Helper_SessionHelper extends Symfony_Component_Templating_Helper_Helper
{
    protected $session;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     */
    public function __construct(Symfony_Component_HttpFoundation_Request $request)
    {
        $this->session = $request->getSession();
    }

    /**
     * Returns an attribute
     *
     * @param string $name    The attribute name
     * @param mixed  $default The default value
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    public function getFlash($name, array $default = array())
    {
        return $this->session->getFlashBag()->get($name, $default);
    }

    public function getFlashes()
    {
        return $this->session->getFlashBag()->all();
    }

    public function hasFlash($name)
    {
        return $this->session->getFlashBag()->has($name);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'session';
    }
}
