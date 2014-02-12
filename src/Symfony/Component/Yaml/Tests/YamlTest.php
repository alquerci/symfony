<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Yaml_Tests_YamlTest extends PHPUnit_Framework_TestCase
{

    public function testParseAndDump()
    {
        $data = array('lorem' => 'ipsum', 'dolor' => 'sit');
        $yml = Symfony_Component_Yaml_Yaml::dump($data);
        $parsed = Symfony_Component_Yaml_Yaml::parse($yml);
        $this->assertEquals($data, $parsed);

        $filename = dirname(__FILE__).'/Fixtures/index.yml';
        $contents = file_get_contents($filename);
        $parsedByFilename = Symfony_Component_Yaml_Yaml::parse($filename);
        $parsedByContents = Symfony_Component_Yaml_Yaml::parse($contents);
        $this->assertEquals($parsedByFilename, $parsedByContents);
    }

    public function testEmbededPhp()
    {
        $filename = dirname(__FILE__).'/Fixtures/embededPhp.yml';
        Symfony_Component_Yaml_Yaml::enablePhpParsing();
        $parsed = Symfony_Component_Yaml_Yaml::parse($filename);
        $this->assertEquals(array('value' => 6), $parsed);
    }

}
