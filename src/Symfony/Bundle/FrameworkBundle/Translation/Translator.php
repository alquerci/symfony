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
 */
class Symfony_Bundle_FrameworkBundle_Translation_Translator extends Symfony_Component_Translation_Translator
{
    protected $container;
    protected $options;
    protected $loaderIds;

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * cache_dir: The cache directory (or null to disable caching)
     *   * debug:     Whether to enable debugging or not (false by default)
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface $container A ContainerInterface instance
     * @param Symfony_Component_Translation_MessageSelector    $selector  The message selector for pluralization
     * @param array              $loaderIds An array of loader Ids
     * @param array              $options   An array of options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container, Symfony_Component_Translation_MessageSelector $selector, $loaderIds = array(), array $options = array())
    {
        $this->container = $container;
        $this->loaderIds = $loaderIds;

        $this->options = array(
            'cache_dir' => null,
            'debug'     => false,
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new InvalidArgumentException(sprintf('The Translator does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        parent::__construct(null, $selector);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        if (null === $this->locale && $this->container->isScopeActive('request') && $this->container->has('request')) {
            $this->locale = $this->container->get('request')->getLocale();
        }

        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadCatalogue($locale)
    {
        if (isset($this->catalogues[$locale])) {
            return;
        }

        if (null === $this->options['cache_dir']) {
            $this->initialize();

            return parent::loadCatalogue($locale);
        }

        $cache = new Symfony_Component_Config_ConfigCache($this->options['cache_dir'].'/catalogue.'.$locale.'.php', $this->options['debug']);
        if (!$cache->isFresh()) {
            $this->initialize();

            parent::loadCatalogue($locale);

            $fallbackContent = '';
            $current = '';
            foreach ($this->computeFallbackLocales($locale) as $fallback) {
                $fallbackContent .= sprintf(<<<EOF
\$catalogue%s = new Symfony_Component_Translation_MessageCatalogue('%s', %s);
\$catalogue%s->addFallbackCatalogue(\$catalogue%s);


EOF
                    ,
                    ucfirst($fallback),
                    $fallback,
                    var_export($this->catalogues[$fallback]->all(), true),
                    ucfirst($current),
                    ucfirst($fallback)
                );
                $current = $fallback;
            }

            $content = sprintf(<<<EOF
<?php

\$catalogue = new Symfony_Component_Translation_MessageCatalogue('%s', %s);

%s
return \$catalogue;

EOF
                ,
                $locale,
                var_export($this->catalogues[$locale]->all(), true),
                $fallbackContent
            );

            $cache->write($content, $this->catalogues[$locale]->getResources());

            return;
        }

        $this->catalogues[$locale] = include $cache->__toString();
    }

    protected function initialize()
    {
        foreach ($this->loaderIds as $id => $aliases) {
            foreach ($aliases as $alias) {
                $this->addLoader($alias, $this->container->get($id));
            }
        }
    }
}
