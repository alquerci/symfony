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
 * Translator.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_Translation_Translator implements Symfony_Component_Translation_TranslatorInterface
{
    /**
     * @var Symfony_Component_Translation_MessageCatalogueInterface[]
     */
    protected $catalogues = array();

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    private $fallbackLocales = array();

    /**
     * @var Symfony_Component_Translation_Loader_LoaderInterface[]
     */
    private $loaders = array();

    /**
     * @var array
     */
    private $resources = array();

    /**
     * @var Symfony_Component_Translation_MessageSelector
     */
    private $selector;

    /**
     * Constructor.
     *
     * @param string               $locale   The locale
     * @param Symfony_Component_Translation_MessageSelector|null $selector The message selector for pluralization
     *
     * @api
     */
    public function __construct($locale, Symfony_Component_Translation_MessageSelector $selector = null)
    {
        $this->locale = $locale;
        $this->selector = $selector ? $selector : new Symfony_Component_Translation_MessageSelector();
    }

    /**
     * Adds a Loader.
     *
     * @param string          $format The name of the loader (@see addResource())
     * @param Symfony_Component_Translation_Loader_LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function addLoader($format, Symfony_Component_Translation_Loader_LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Adds a Resource.
     *
     * @param string $format   The name of the loader (@see addLoader())
     * @param mixed  $resource The resource name
     * @param string $locale   The locale
     * @param string $domain   The domain
     *
     * @api
     */
    public function addResource($format, $resource, $locale, $domain = 'messages')
    {
        $this->resources[$locale][] = array($format, $resource, $domain);

        unset($this->catalogues[$locale]);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the fallback locale(s).
     *
     * @param string|array $locales The fallback locale(s)
     *
     * @api
     */
    public function setFallbackLocale($locales)
    {
        // needed as the fallback locales are linked to the already loaded catalogues
        $this->catalogues = array();

        $this->fallbackLocales = (array) $locales;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return strtr($this->catalogues[$locale]->get((string) is_object($id) && method_exists($id, '__toString') ? $id->__toString() : $id, $domain), $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        $id = (string) is_object($id) && method_exists($id, '__toString') ? $id->__toString() : $id;

        $catalogue = $this->catalogues[$locale];
        while (!$catalogue->defines($id, $domain)) {
            if ($cat = $catalogue->getFallbackCatalogue()) {
                $catalogue = $cat;
                $locale = $catalogue->getLocale();
            } else {
                break;
            }
        }

        return strtr($this->selector->choose($catalogue->get($id, $domain), (int) $number, $locale), $parameters);
    }

    protected function loadCatalogue($locale)
    {
        try {
            $this->doLoadCatalogue($locale);
        } catch (Symfony_Component_Translation_Exception_NotFoundResourceException $e) {
            if (!$this->computeFallbackLocales($locale)) {
                throw $e;
            }
        }
        $this->loadFallbackCatalogues($locale);
    }

    private function doLoadCatalogue($locale)
    {
        $this->catalogues[$locale] = new Symfony_Component_Translation_MessageCatalogue($locale);

        if (isset($this->resources[$locale])) {
            foreach ($this->resources[$locale] as $resource) {
                if (!isset($this->loaders[$resource[0]])) {
                    throw new RuntimeException(sprintf('The "%s" translation loader is not registered.', $resource[0]));
                }
                $this->catalogues[$locale]->addCatalogue($this->loaders[$resource[0]]->load($resource[1], $locale, $resource[2]));
            }
        }
    }

    private function loadFallbackCatalogues($locale)
    {
        $current = $this->catalogues[$locale];

        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                $this->doLoadCatalogue($fallback);
            }

            $current->addFallbackCatalogue($this->catalogues[$fallback]);
            $current = $this->catalogues[$fallback];
        }
    }

    protected function computeFallbackLocales($locale)
    {
        $locales = array();
        foreach ($this->fallbackLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }

            $locales[] = $fallback;
        }

        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }

        return array_unique($locales);
    }
}
