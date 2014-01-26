<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Functional_Bundle_TestBundle_Controller_SessionController extends Symfony_Component_DependencyInjection_ContainerAware
{
    public function welcomeAction($name=null)
    {
        $request = $this->container->get('request');
        $session = $request->getSession();

        // new session case
        if (!$session->has('name')) {
            if (!$name) {
                return new Symfony_Component_HttpFoundation_Response('You are new here and gave no name.');
            }

            // remember name
            $session->set('name', $name);

            return new Symfony_Component_HttpFoundation_Response(sprintf('Hello %s, nice to meet you.', $name));
        }

        // existing session
        $name = $session->get('name');

        return new Symfony_Component_HttpFoundation_Response(sprintf('Welcome back %s, nice to meet you.', $name));
    }

    public function logoutAction()
    {
        $request = $this->container->get('request')->getSession('session')->invalidate();

        return new Symfony_Component_HttpFoundation_Response('Session cleared.');
    }

    public function setFlashAction($message)
    {
        $request = $this->container->get('request');
        $session = $request->getSession();
        $session->getFlashBag()->set('notice', $message);

        return new Symfony_Component_HttpFoundation_RedirectResponse($this->container->get('router')->generate('session_showflash'));
    }

    public function showFlashAction()
    {
        $request = $this->container->get('request');
        $session = $request->getSession();

        if ($session->getFlashBag()->has('notice')) {
            list($output) = $session->getFlashBag()->get('notice');
        } else {
            $output = 'No flash was set.';
        }

        return new Symfony_Component_HttpFoundation_Response($output);
    }
}
