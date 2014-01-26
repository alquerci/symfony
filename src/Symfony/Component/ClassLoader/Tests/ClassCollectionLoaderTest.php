<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (version_compare(phpversion(), '5.3.0', '>=')) {
    require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/GInterface.php';
    require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/CInterface.php';
    require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/B.php';
    require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/A.php';
}

class Symfony_Component_ClassLoader_Tests_ClassCollectionLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testTraitDependencies()
    {
        if (version_compare(phpversion(), '5.4', '<')) {
            $this->markTestSkipped('Requires PHP > 5.4');

            return;
        }

        require_once dirname(__FILE__).'/Fixtures/deps/traits.php';

        $r = new ReflectionClass('Symfony_Component_ClassLoader_ClassCollectionLoader');
        $m = $r->getMethod('getOrderedClasses');
        $m->setAccessible(true);

        $ordered = $m->invoke('Symfony_Component_ClassLoader_ClassCollectionLoader', array('CTFoo'));

        $this->assertEquals(
            array('TD', 'TC', 'TB', 'TA', 'TZ', 'CTFoo'),
            array_map(create_function('$class', 'return $class->getName();'), $ordered)
        );

        $ordered = $m->invoke('Symfony_Component_ClassLoader_ClassCollectionLoader', array('CTBar'));

        $this->assertEquals(
            array('TD', 'TZ', 'TC', 'TB', 'TA', 'CTBar'),
            array_map(create_function('$class', 'return $class->getName();'), $ordered)
        );
    }

    /**
     * @dataProvider getDifferentOrders
     */
    public function testClassReordering(array $classes)
    {
        if (version_compare(phpversion(), '5.3', '<')) {
            $this->markTestSkipped('Requires PHP > 5.3');

            return;
        }

        $expected = array(
            'ClassesWithParents\\GInterface',
            'ClassesWithParents\\CInterface',
            'ClassesWithParents\\B',
            'ClassesWithParents\\A',
        );

        $r = new ReflectionClass('Symfony_Component_ClassLoader_ClassCollectionLoader');
        $m = $r->getMethod('getOrderedClasses');
        $m->setAccessible(true);

        $ordered = $m->invoke('Symfony_Component_ClassLoader_ClassCollectionLoader', $classes);

        $this->assertEquals($expected, array_map(create_function('$class', 'return $class->getName();'), $ordered));
    }

    public function getDifferentOrders()
    {
        return array(
            array(array(
                'ClassesWithParents\\A',
                'ClassesWithParents\\CInterface',
                'ClassesWithParents\\GInterface',
                'ClassesWithParents\\B',
            )),
            array(array(
                'ClassesWithParents\\B',
                'ClassesWithParents\\A',
                'ClassesWithParents\\CInterface',
            )),
            array(array(
                'ClassesWithParents\\CInterface',
                'ClassesWithParents\\B',
                'ClassesWithParents\\A',
            )),
            array(array(
                'ClassesWithParents\\A',
            )),
        );
    }

    /**
     * @dataProvider getDifferentOrdersForTraits
     */
    public function testClassWithTraitsReordering(array $classes)
    {
        if (version_compare(phpversion(), '5.4', '<')) {
            $this->markTestSkipped('Requires PHP > 5.4');

            return;
        }

        require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/ATrait.php';
        require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/BTrait.php';
        require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/CTrait.php';
        require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/D.php';
        require_once dirname(__FILE__).'/Fixtures/ClassesWithParents/E.php';

        $expected = array(
            'ClassesWithParents\\GInterface',
            'ClassesWithParents\\CInterface',
            'ClassesWithParents\\ATrait',
            'ClassesWithParents\\BTrait',
            'ClassesWithParents\\CTrait',
            'ClassesWithParents\\B',
            'ClassesWithParents\\A',
            'ClassesWithParents\\D',
            'ClassesWithParents\\E',
        );

        $r = new ReflectionClass('Symfony_Component_ClassLoader_ClassCollectionLoader');
        $m = $r->getMethod('getOrderedClasses');
        $m->setAccessible(true);

        $ordered = $m->invoke('Symfony_Component_ClassLoader_ClassCollectionLoader', $classes);

        $this->assertEquals($expected, array_map(create_function('$class', 'return $class->getName();'), $ordered));
    }

    public function getDifferentOrdersForTraits()
    {
        return array(
            array(array(
                'ClassesWithParents\\E',
                'ClassesWithParents\\ATrait',
            )),
            array(array(
                'ClassesWithParents\\E',
            )),
        );
    }

    /**
     * @dataProvider getFixNamespaceDeclarationsData
     */
    public function testFixNamespaceDeclarations($source, $expected)
    {
        if (version_compare(phpversion(), '5.3', '<')) {
            $this->markTestSkipped('Requires PHP > 5.3');

            return;
        }

        $this->assertEquals('<?php '.$expected, Symfony_Component_ClassLoader_ClassCollectionLoader::fixNamespaceDeclarations('<?php '.$source));
    }

    public function getFixNamespaceDeclarationsData()
    {
        return array(
            array("namespace;\nclass Foo {}\n", "namespace\n{\nclass Foo {}\n}"),
            array("namespace Foo;\nclass Foo {}\n", "namespace Foo\n{\nclass Foo {}\n}"),
            array("namespace   Bar ;\nclass Foo {}\n", "namespace Bar\n{\nclass Foo {}\n}"),
            array("namespace Foo\Bar;\nclass Foo {}\n", "namespace Foo\Bar\n{\nclass Foo {}\n}"),
            array("namespace Foo\Bar\Bar\n{\nclass Foo {}\n}\n", "namespace Foo\Bar\Bar\n{\nclass Foo {}\n}"),
            array("namespace\n{\nclass Foo {}\n}\n", "namespace\n{\nclass Foo {}\n}"),
        );
    }

    /**
     * @dataProvider getFixNamespaceDeclarationsDataWithoutTokenizer
     */
    public function testFixNamespaceDeclarationsWithoutTokenizer($source, $expected)
    {
        if (version_compare(phpversion(), '5.3', '<')) {
            $this->markTestSkipped('Requires PHP > 5.3');

            return;
        }

        Symfony_Component_ClassLoader_ClassCollectionLoader::enableTokenizer(false);
        $this->assertEquals('<?php '.$expected, Symfony_Component_ClassLoader_ClassCollectionLoader::fixNamespaceDeclarations('<?php '.$source));
        Symfony_Component_ClassLoader_ClassCollectionLoader::enableTokenizer(true);
    }

    public function getFixNamespaceDeclarationsDataWithoutTokenizer()
    {
        return array(
            array("namespace;\nclass Foo {}\n", "namespace\n{\nclass Foo {}\n}\n"),
            array("namespace Foo;\nclass Foo {}\n", "namespace Foo\n{\nclass Foo {}\n}\n"),
            array("namespace   Bar ;\nclass Foo {}\n", "namespace   Bar\n{\nclass Foo {}\n}\n"),
            array("namespace Foo\Bar;\nclass Foo {}\n", "namespace Foo\Bar\n{\nclass Foo {}\n}\n"),
            array("namespace Foo\Bar\Bar\n{\nclass Foo {}\n}\n", "namespace Foo\Bar\Bar\n{\nclass Foo {}\n}\n"),
            array("namespace\n{\nclass Foo {}\n}\n", "namespace\n{\nclass Foo {}\n}\n"),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnableToLoadClassException()
    {
        if (is_file($file = sys_get_temp_dir().'/foo.php')) {
            unlink($file);
        }

        Symfony_Component_ClassLoader_ClassCollectionLoader::load(array('SomeNotExistingClass'), sys_get_temp_dir(), 'foo', false);
    }

    public function testCommentStripping()
    {
        if (version_compare(phpversion(), '5.3', '<')) {
            $this->markTestSkipped('Requires PHP > 5.3');

            return;
        }

        if (is_file($file = sys_get_temp_dir().'/bar.php')) {
            unlink($file);
        }
        spl_autoload_register($r = create_function('$class', '
            if (0 === strpos($class, "Namespaced") || 0 === strpos($class, "Pearlike_")) {
                require_once dirname(__FILE__)."/Fixtures/".str_replace(array("\\x5C", "_"), "/", $class).".php";
            }
        '));

        Symfony_Component_ClassLoader_ClassCollectionLoader::load(
            array('Namespaced\\WithComments', 'Pearlike_WithComments'),
            sys_get_temp_dir(),
            'bar',
            false
        );

        spl_autoload_unregister($r);

        $this->assertEquals(<<<EOF
namespace Namespaced
{
class WithComments
{
public static \$loaded = true;
}
\$string ='string shoult not be   modified {\$string}';
\$heredoc = (<<<HD


Heredoc should not be   modified {\$string}


HD
);
\$nowdoc =<<<'ND'


Nowdoc should not be   modified {\$string}


ND
;
}
namespace
{
class Pearlike_WithComments
{
public static \$loaded = true;
}
}
EOF
        , str_replace("<?php \n", '', file_get_contents($file)));

        unlink($file);
    }
}
