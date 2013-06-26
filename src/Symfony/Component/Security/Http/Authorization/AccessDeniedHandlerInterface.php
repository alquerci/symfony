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
 * This is used by the ExceptionListener to translate an AccessDeniedException
 * to a Response object.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Http_Authorization_AccessDeniedHandlerInterface
{
    /**
     * Handles an access denied failure.
     *
     * @param Symfony_Component_HttpFoundation_Request               $request
     * @param Symfony_Component_Security_Core_Exception_AccessDeniedException $accessDeniedException
     *
     * @return Response may return null
     */
    public function handle(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AccessDeniedException $accessDeniedException);
}
