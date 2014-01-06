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
 * HttpKernelInterface handles a Request to convert it to a Response.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_HttpKernel_HttpKernelInterface
{
    const MASTER_REQUEST = 1;
    const SUB_REQUEST = 2;

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     * @param integer $type    The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     *
     * @throws Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernel::MASTER_REQUEST, $catch = true);
}
