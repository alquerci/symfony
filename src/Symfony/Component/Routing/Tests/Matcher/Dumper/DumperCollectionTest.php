<?php

class Symfony_Component_Routing_Test_Matcher_Dumper_DumperCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testGetRoot()
    {
        $a = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();

        $b = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();
        $a->add($b);

        $c = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();
        $b->add($c);

        $d = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();
        $c->add($d);

        $this->assertSame($a, $c->getRoot());
    }
}
