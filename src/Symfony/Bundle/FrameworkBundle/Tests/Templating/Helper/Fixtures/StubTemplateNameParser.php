<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_Fixtures_StubTemplateNameParser implements Symfony_Component_Templating_TemplateNameParserInterface
{
    private $root;

    private $rootTheme;

    public function __construct($root, $rootTheme)
    {
        $this->root = $root;
        $this->rootTheme = $rootTheme;
    }

    public function parse($name)
    {
        list($bundle, $controller, $template) = explode(':', $name, 3);

        if ($template[0] == '_') {
            $path = $this->rootTheme.'/Custom/'.$template;
        } elseif ($bundle === 'TestBundle') {
            $path = $this->rootTheme.'/'.$controller.'/'.$template;
        } else {
            $path = $this->root.'/'.$controller.'/'.$template;
        }

        return new Symfony_Component_Templating_TemplateReference($path, 'php');
    }
}
