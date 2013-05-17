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
 * Finds all the templates.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class Symfony_Bundle_FrameworkBundle_CacheWarmer_TemplateFinder implements Symfony_Bundle_FrameworkBundle_CacheWarmer_TemplateFinderInterface
{
    private $kernel;
    private $parser;
    private $rootDir;
    private $templates;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_HttpKernelInterface             $kernel  A KernelInterface instance
     * @param Symfony_Component_Templating_TemplateNameParserInterface $parser  A TemplateNameParserInterface instance
     * @param string                      $rootDir The directory where global templates can be stored
     */
    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, Symfony_Component_Templating_TemplateNameParserInterface $parser, $rootDir)
    {
        $this->kernel = $kernel;
        $this->parser = $parser;
        $this->rootDir = $rootDir;
    }

    /**
     * Find all the templates in the bundle and in the kernel Resources folder.
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    public function findAllTemplates()
    {
        if (null !== $this->templates) {
            return $this->templates;
        }

        $templates = array();

        foreach ($this->kernel->getBundles() as $name => $bundle) {
            $templates = array_merge($templates, $this->findTemplatesInBundle($bundle));
        }

        $templates = array_merge($templates, $this->findTemplatesInFolder($this->rootDir.'/views'));

        return $this->templates = $templates;
    }

    /**
     * Find templates in the given directory.
     *
     * @param string $dir The folder where to look for templates
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    private function findTemplatesInFolder($dir)
    {
        $templates = array();

        if (is_dir($dir)) {
            $finder = new Symfony_Component_Finder_Finder();
            foreach ($finder->files()->followLinks()->in($dir) as $file) {
                $template = $this->parser->parse($file->getRelativePathname());
                if (false !== $template) {
                    $templates[] = $template;
                }
            }
        }

        return $templates;
    }

    /**
     * Find templates in the given bundle.
     *
     * @param Symfony_Component_HttpKernel_Bundle_BundleInterface $bundle The bundle where to look for templates
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    private function findTemplatesInBundle(Symfony_Component_HttpKernel_Bundle_BundleInterface $bundle)
    {
        $templates = $this->findTemplatesInFolder($bundle->getPath().'/Resources/views');
        $name = $bundle->getName();

        foreach ($templates as $i => $template) {
            $templates[$i] = $template->set('bundle', $name);
        }

        return $templates;
    }
}
