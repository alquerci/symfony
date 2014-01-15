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
 * Client simulates a browser and makes requests to a Kernel object.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_HttpKernel_Client extends Symfony_Component_BrowserKit_Client
{
    protected $kernel;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_HttpKernelInterface $kernel    An HttpKernel instance
     * @param array               $server    The server parameters (equivalent of $_SERVER)
     * @param Symfony_Component_BrowserKit_History             $history   A History instance to store the browser history
     * @param Symfony_Component_BrowserKit_CookieJar           $cookieJar A CookieJar instance to store the cookies
     */
    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, array $server = array(), Symfony_Component_BrowserKit_History $history = null, Symfony_Component_BrowserKit_CookieJar $cookieJar = null)
    {
        $this->kernel = $kernel;

        parent::__construct($server, $history, $cookieJar);

        $this->followRedirects = false;
    }

    /**
     * Makes a request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     */
    protected function doRequest($request)
    {
        $response = $this->kernel->handle($request);

        if ($this->kernel instanceof Symfony_Component_HttpKernel_TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }

        return $response;
    }

    /**
     * Returns the script to execute when the request must be insulated.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return string
     */
    protected function getScript($request)
    {
        $kernel = str_replace("'", "\\'", serialize($this->kernel));
        $request = str_replace("'", "\\'", serialize($request));

        $r = new ReflectionClass('Symfony_Component_ClassLoader_ClassLoader');
        $requirePath = str_replace("'", "\\'", $r->getFileName());
        $symfonyPath = str_replace("'", "\\'", realpath(__DIR__.'/../../..'));

        return <<<EOF
<?php

require_once '$requirePath';

\$loader = new Symfony_Component_ClassLoader_ClassLoader();
\$loader->addPrefix('Symfony', '$symfonyPath');
\$loader->register();

\$kernel = unserialize('$kernel');
echo serialize(\$kernel->handle(unserialize('$request')));
EOF;
    }

    /**
     * Converts the BrowserKit request to a HttpKernel request.
     *
     * @param Symfony_Component_BrowserKit_Request $request A Request instance
     *
     * @return Symfony_Component_HttpFoundation_Request A Request instance
     */
    protected function filterRequest(Symfony_Component_BrowserKit_Request $request)
    {
        $httpRequest = Symfony_Component_HttpFoundation_Request::create($request->getUri(), $request->getMethod(), $request->getParameters(), $request->getCookies(), $request->getFiles(), $request->getServer(), $request->getContent());

        $httpRequest->files->replace($this->filterFiles($httpRequest->files->all()));

        return $httpRequest;
    }

    /**
     * Filters an array of files.
     *
     * This method created test instances of UploadedFile so that the move()
     * method can be called on those instances.
     *
     * If the size of a file is greater than the allowed size (from php.ini) then
     * an invalid UploadedFile is returned with an error set to UPLOAD_ERR_INI_SIZE.
     *
     * @see Symfony_Component_HttpFoundation_File_UploadedFile
     *
     * @param array $files An array of files
     *
     * @return array An array with all uploaded files marked as already moved
     */
    protected function filterFiles(array $files)
    {
        $filtered = array();
        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = $this->filterFiles($value);
            } elseif ($value instanceof Symfony_Component_HttpFoundation_File_UploadedFile) {
                if ($value->isValid() && $value->getSize() > Symfony_Component_HttpFoundation_File_UploadedFile::getMaxFilesize()) {
                    $filtered[$key] = new Symfony_Component_HttpFoundation_File_UploadedFile(
                        '',
                        $value->getClientOriginalName(),
                        $value->getClientMimeType(),
                        0,
                        UPLOAD_ERR_INI_SIZE,
                        true
                    );
                } else {
                    $filtered[$key] = new Symfony_Component_HttpFoundation_File_UploadedFile(
                        $value->getPathname(),
                        $value->getClientOriginalName(),
                        $value->getClientMimeType(),
                        $value->getClientSize(),
                        $value->getError(),
                        true
                    );
                }
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Converts the HttpKernel response to a BrowserKit response.
     *
     * @param Response $response A Response instance
     *
     * @return Response A Response instance
     */
    protected function filterResponse($response)
    {
        $headers = $response->headers->all();
        if ($response->headers->getCookies()) {
            $cookies = array();
            foreach ($response->headers->getCookies() as $cookie) {
                $cookies[] = new Symfony_Component_BrowserKit_Cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
            }
            $headers['Set-Cookie'] = $cookies;
        }

        // this is needed to support StreamedResponse
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        return new Symfony_Component_BrowserKit_Response($content, $response->getStatusCode(), $headers);
    }
}
