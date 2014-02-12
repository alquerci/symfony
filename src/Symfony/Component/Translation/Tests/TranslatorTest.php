<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Translation_Tests_TranslatorTest extends PHPUnit_Framework_TestCase
{
    public function testSetGetLocale()
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());

        $this->assertEquals('en', $translator->getLocale());

        $translator->setLocale('fr');
        $this->assertEquals('fr', $translator->getLocale());
    }

    public function testSetFallbackLocale()
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en');
        $translator->addResource('array', array('bar' => 'foobar'), 'fr');

        // force catalogue loading
        $translator->trans('bar');

        $translator->setFallbackLocale('fr');
        $this->assertEquals('foobar', $translator->trans('bar'));
    }

    public function testSetFallbackLocaleMultiple()
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('foo' => 'foo (en)'), 'en');
        $translator->addResource('array', array('bar' => 'bar (fr)'), 'fr');

        // force catalogue loading
        $translator->trans('bar');

        $translator->setFallbackLocale(array('fr_FR', 'fr'));
        $this->assertEquals('bar (fr)', $translator->trans('bar'));
    }

    public function testTransWithFallbackLocale()
    {
        $translator = new Symfony_Component_Translation_Translator('fr_FR', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en_US');
        $translator->addResource('array', array('bar' => 'foobar'), 'en');

        $translator->setFallbackLocale('en');

        $this->assertEquals('foobar', $translator->trans('bar'));
    }

    /**
     * @dataProvider      getTransFileTests
     * @expectedException Symfony_Component_Translation_Exception_NotFoundResourceException
     */
    public function testTransWithoutFallbackLocaleFile($format, $loader)
    {
        $loaderClass = 'Symfony_Component_Translation_Loader_'.$loader;
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader($format, new $loaderClass());
        $translator->addResource($format, dirname(__FILE__).'/fixtures/non-existing', 'en');
        $translator->addResource($format, dirname(__FILE__).'/fixtures/resources.'.$format, 'en');

        // force catalogue loading
        $translator->trans('foo');
    }

    /**
     * @dataProvider getTransFileTests
     */
    public function testTransWithFallbackLocaleFile($format, $loader)
    {
        $loaderClass = 'Symfony_Component_Translation_Loader_'.$loader;
        $translator = new Symfony_Component_Translation_Translator('en_GB', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader($format, new $loaderClass());
        $translator->addResource($format, dirname(__FILE__).'/fixtures/non-existing', 'en_GB');
        $translator->addResource($format, dirname(__FILE__).'/fixtures/resources.'.$format, 'en', 'resources');

        $this->assertEquals('bar', $translator->trans('foo', array(), 'resources'));
    }

    public function testTransWithFallbackLocaleBis()
    {
        $translator = new Symfony_Component_Translation_Translator('en_US', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en_US');
        $translator->addResource('array', array('bar' => 'foobar'), 'en');
        $this->assertEquals('foobar', $translator->trans('bar'));
    }

    public function testTransWithFallbackLocaleTer()
    {
        $translator = new Symfony_Component_Translation_Translator('fr_FR', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('foo' => 'foo (en_US)'), 'en_US');
        $translator->addResource('array', array('bar' => 'bar (en)'), 'en');

        $translator->setFallbackLocale(array('en_US', 'en'));

        $this->assertEquals('foo (en_US)', $translator->trans('foo'));
        $this->assertEquals('bar (en)', $translator->trans('bar'));
    }

    public function testTransNonExistentWithFallback()
    {
        $translator = new Symfony_Component_Translation_Translator('fr', new Symfony_Component_Translation_MessageSelector());
        $translator->setFallbackLocale('en');
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $this->assertEquals('non-existent', $translator->trans('non-existent'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWhenAResourceHasNoRegisteredLoader()
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addResource('array', array('foo' => 'foofoo'), 'en');

        $translator->trans('foo');
    }

    /**
     * @dataProvider getTransTests
     */
    public function testTrans($expected, $id, $translation, $parameters, $locale, $domain)
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array((string) ((is_object($id) && method_exists($id, '__toString')) ? $id->__toString() : $id) => $translation), $locale, $domain);

        $this->assertEquals($expected, $translator->trans($id, $parameters, $domain, $locale));
    }

    /**
     * @dataProvider getFlattenedTransTests
     */
    public function testFlattenedTrans($expected, $messages, $id)
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', $messages, 'fr', '');

        $this->assertEquals($expected, $translator->trans($id, array(), '', 'fr'));
    }

    /**
     * @dataProvider getTransChoiceTests
     */
    public function testTransChoice($expected, $id, $translation, $number, $parameters, $locale, $domain)
    {
        $translator = new Symfony_Component_Translation_Translator('en', new Symfony_Component_Translation_MessageSelector());
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array((string) ((is_object($id) && method_exists($id, '__toString')) ? $id->__toString() : $id) => $translation), $locale, $domain);

        $this->assertEquals($expected, $translator->transChoice($id, $number, $parameters, $domain, $locale));
    }

    public function getTransFileTests()
    {
        return array(
//             array('csv', 'CsvFileLoader'),
//             array('ini', 'IniFileLoader'),
//             array('mo', 'MoFileLoader'),
//             array('po', 'PoFileLoader'),
            array('php', 'PhpFileLoader'),
//             array('ts', 'QtFileLoader'),
            array('xlf', 'XliffFileLoader'),
            array('yml', 'YamlFileLoader'),
        );
    }

    public function getTransTests()
    {
        return array(
            array('Symfony2 est super !', 'Symfony2 is great!', 'Symfony2 est super !', array(), 'fr', ''),
            array('Symfony2 est awesome !', 'Symfony2 is %what%!', 'Symfony2 est %what% !', array('%what%' => 'awesome'), 'fr', ''),
            array('Symfony2 est super !', new Symfony_Component_Translation_Tests_String('Symfony2 is great!'), 'Symfony2 est super !', array(), 'fr', ''),
        );
    }

    public function getFlattenedTransTests()
    {
        $messages = array(
            'symfony2' => array(
                'is' => array(
                    'great' => 'Symfony2 est super!'
                )
            ),
            'foo' => array(
                'bar' => array(
                    'baz' => 'Foo Bar Baz'
                ),
                'baz' => 'Foo Baz',
            ),
        );

        return array(
            array('Symfony2 est super!', $messages, 'symfony2.is.great'),
            array('Foo Bar Baz', $messages, 'foo.bar.baz'),
            array('Foo Baz', $messages, 'foo.baz'),
        );
    }

    public function getTransChoiceTests()
    {
        return array(
            array('Il y a 0 pomme', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', '{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il y a 0 pomme', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il y a 0 pomme', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il n\'y a aucune pomme', '{0} There is no apple|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
            array('Il y a 1 pomme', '{0} There is no apple|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 1, array('%count%' => 1), 'fr', ''),
            array('Il y a 10 pommes', '{0} There is no apple|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 10, array('%count%' => 10), 'fr', ''),

            array('Il y a 0 pomme', new Symfony_Component_Translation_Tests_String('{0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples'), '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, array('%count%' => 0), 'fr', ''),
        );
    }

    public function testTransChoiceFallback()
    {
        $translator = new Symfony_Component_Translation_Translator('ru', new Symfony_Component_Translation_MessageSelector());
        $translator->setFallbackLocale('en');
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('some_message2' => 'one thing|%count% things'), 'en');

        $this->assertEquals('10 things', $translator->transChoice('some_message2', 10, array('%count%' => 10)));
    }

    public function testTransChoiceFallbackBis()
    {
        $translator = new Symfony_Component_Translation_Translator('ru', new Symfony_Component_Translation_MessageSelector());
        $translator->setFallbackLocale(array('en_US', 'en'));
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());
        $translator->addResource('array', array('some_message2' => 'one thing|%count% things'), 'en_US');

        $this->assertEquals('10 things', $translator->transChoice('some_message2', 10, array('%count%' => 10)));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTransChoiceFallbackWithNoTranslation()
    {
        $translator = new Symfony_Component_Translation_Translator('ru', new Symfony_Component_Translation_MessageSelector());
        $translator->setFallbackLocale('en');
        $translator->addLoader('array', new Symfony_Component_Translation_Loader_ArrayLoader());

        $this->assertEquals('10 things', $translator->transChoice('some_message2', 10, array('%count%' => 10)));
    }
}

class Symfony_Component_Translation_Tests_String
{
    protected $str;

    public function __construct($str)
    {
        $this->str = $str;
    }

    public function __toString()
    {
        return $this->str;
    }
}
