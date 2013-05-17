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
 * TemplateFilenameParser converts template filenames to
 * TemplateReferenceInterface instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_TemplateFilenameParser implements Symfony_Component_Templating_TemplateNameParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($file)
    {
        $parts = explode('/', strtr($file, '\\', '/'));

        $elements = explode('.', array_pop($parts));
        if (3 > count($elements)) {
            return false;
        }
        $engine = array_pop($elements);
        $format = array_pop($elements);

        return new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('', implode('/', $parts), implode('.', $elements), $format, $engine);
    }
}
