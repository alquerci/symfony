<?php

class Symfony_Component_Routing_Tests_Matcher_Dumper_DumperPrefixCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testAddPrefixRoute()
    {
        $coll = new Symfony_Component_Routing_Matcher_Dumper_DumperPrefixCollection;
        $coll->setPrefix('');

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar', new Symfony_Component_Routing_Route('/foo/bar'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar2', new Symfony_Component_Routing_Route('/foo/bar'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('qux', new Symfony_Component_Routing_Route('/foo/qux'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar3', new Symfony_Component_Routing_Route('/foo/bar'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar4', new Symfony_Component_Routing_Route(''));
        $result = $coll->addPrefixRoute($route);

        $expect = <<<EOF
            |-coll /
            | |-coll /f
            | | |-coll /fo
            | | | |-coll /foo
            | | | | |-coll /foo/
            | | | | | |-coll /foo/b
            | | | | | | |-coll /foo/ba
            | | | | | | | |-coll /foo/bar
            | | | | | | | | |-route bar /foo/bar
            | | | | | | | | |-route bar2 /foo/bar
            | | | | | |-coll /foo/q
            | | | | | | |-coll /foo/qu
            | | | | | | | |-coll /foo/qux
            | | | | | | | | |-route qux /foo/qux
            | | | | | |-coll /foo/b
            | | | | | | |-coll /foo/ba
            | | | | | | | |-coll /foo/bar
            | | | | | | | | |-route bar3 /foo/bar
            | |-route bar4 /

EOF;

        $this->assertSame($expect, $this->collectionToString($result->getRoot(), '            '));
    }

    public function testMergeSlashNodes()
    {
        $coll = new Symfony_Component_Routing_Matcher_Dumper_DumperPrefixCollection;
        $coll->setPrefix('');

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar', new Symfony_Component_Routing_Route('/foo/bar'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar2', new Symfony_Component_Routing_Route('/foo/bar'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('qux', new Symfony_Component_Routing_Route('/foo/qux'));
        $coll = $coll->addPrefixRoute($route);

        $route = new Symfony_Component_Routing_Matcher_Dumper_DumperRoute('bar3', new Symfony_Component_Routing_Route('/foo/bar'));
        $result = $coll->addPrefixRoute($route);

        $result->getRoot()->mergeSlashNodes();

        $expect = <<<EOF
            |-coll /f
            | |-coll /fo
            | | |-coll /foo
            | | | |-coll /foo/b
            | | | | |-coll /foo/ba
            | | | | | |-coll /foo/bar
            | | | | | | |-route bar /foo/bar
            | | | | | | |-route bar2 /foo/bar
            | | | |-coll /foo/q
            | | | | |-coll /foo/qu
            | | | | | |-coll /foo/qux
            | | | | | | |-route qux /foo/qux
            | | | |-coll /foo/b
            | | | | |-coll /foo/ba
            | | | | | |-coll /foo/bar
            | | | | | | |-route bar3 /foo/bar

EOF;

        $this->assertSame($expect, $this->collectionToString($result->getRoot(), '            '));
    }

    private function collectionToString(Symfony_Component_Routing_Matcher_Dumper_DumperCollection $collection, $prefix)
    {
        $string = '';
        foreach ($collection as $route) {
            if ($route instanceof Symfony_Component_Routing_Matcher_Dumper_DumperCollection) {
                $string .= sprintf("%s|-coll %s\n", $prefix, $route->getPrefix());
                $string .= $this->collectionToString($route, $prefix.'| ');
            } else {
                $string .= sprintf("%s|-route %s %s\n", $prefix, $route->getName(), $route->getRoute()->getPath());
            }
        }

        return $string;
    }
}
