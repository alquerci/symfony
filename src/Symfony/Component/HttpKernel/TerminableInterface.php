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
 * Terminable extends the Kernel request/response cycle with dispatching a post
 * response event after sending the response and before shutting down the kernel.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Pierre Minnieur <pierre.minnieur@sensiolabs.de>
 *
 * @api
 */
interface Symfony_Component_HttpKernel_TerminableInterface
{
    /**
     * Terminates a request/response cycle.
     *
     * Should be called after sending the response and before shutting down the kernel.
     *
     * @param Symfony_Component_HttpFoundation_Request  $request  A Request instance
     * @param Symfony_Component_HttpFoundation_Response $response A Response instance
     *
     * @api
     */
    public function terminate(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response);
}
