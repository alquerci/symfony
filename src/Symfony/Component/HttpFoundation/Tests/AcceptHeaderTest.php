<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpFoundation_Tests_AcceptHeaderTest extends PHPUnit_Framework_TestCase
{
    public function testFirst()
    {
        $header = Symfony_Component_HttpFoundation_AcceptHeader::fromString('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
        $this->assertSame('text/html', $header->first()->getValue());
    }

    /**
     * @dataProvider provideFromStringData
     */
    public function testFromString($string, array $items)
    {
        $header = Symfony_Component_HttpFoundation_AcceptHeader::fromString($string);
        $parsed = array_values($header->all());
        // reset index since the fixtures don't have them set
        foreach ($parsed as $item) {
            $item->setIndex(0);
        }
        $this->assertEquals($items, $parsed);
    }

    public function provideFromStringData()
    {
        return array(
            array('', array()),
            array('gzip', array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('gzip'))),
            array('gzip,deflate,sdch', array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('gzip'), new Symfony_Component_HttpFoundation_AcceptHeaderItem('deflate'), new Symfony_Component_HttpFoundation_AcceptHeaderItem('sdch'))),
            array("gzip, deflate\t,sdch", array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('gzip'), new Symfony_Component_HttpFoundation_AcceptHeaderItem('deflate'), new Symfony_Component_HttpFoundation_AcceptHeaderItem('sdch'))),
            array('"this;should,not=matter"', array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('this;should,not=matter'))),
        );
    }

    /**
     * @dataProvider provideToStringData
     */
    public function testToString(array $items, $string)
    {
        $header = new Symfony_Component_HttpFoundation_AcceptHeader($items);
        $this->assertEquals($string, (string) $header->__toString());
    }

    public function provideToStringData()
    {
        return array(
            array(array(), ''),
            array(array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('gzip')), 'gzip'),
            array(array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('gzip'), new Symfony_Component_HttpFoundation_AcceptHeaderItem('deflate'), new Symfony_Component_HttpFoundation_AcceptHeaderItem('sdch')), 'gzip,deflate,sdch'),
            array(array(new Symfony_Component_HttpFoundation_AcceptHeaderItem('this;should,not=matter')), 'this;should,not=matter'),
        );
    }

    /**
     * @dataProvider provideFilterData
     */
    public function testFilter($string, $filter, array $values)
    {
        $header = Symfony_Component_HttpFoundation_AcceptHeader::fromString($string)->filter($filter);
        $this->assertEquals($values, array_keys($header->all()));
    }

    public function provideFilterData()
    {
        return array(
            array('fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4', '/fr.*/', array('fr-FR', 'fr')),
        );
    }

    /**
     * @dataProvider provideSortingData
     */
    public function testSorting($string, array $values)
    {
        $header = Symfony_Component_HttpFoundation_AcceptHeader::fromString($string);
        $this->assertEquals($values, array_keys($header->all()));
    }

    public function provideSortingData()
    {
        return array(
            'quality has priority' => array('*;q=0.3,ISO-8859-1,utf-8;q=0.7',  array('ISO-8859-1', 'utf-8', '*')),
            'order matters when q is equal' => array('*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7',  array('ISO-8859-1', 'utf-8', '*')),
            'order matters when q is equal2' => array('*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7',  array('utf-8', 'ISO-8859-1', '*')),
        );
    }
}
