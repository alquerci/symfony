<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Expression_ExpressionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTypeGuesserData
     */
    public function testTypeGuesser($expr, $type)
    {
        $this->assertEquals($type, Symfony_Component_Finder_Expression_Expression::create($expr)->getType());
    }

    /**
     * @dataProvider getCaseSensitiveData
     */
    public function testCaseSensitive($expr, $isCaseSensitive)
    {
        $this->assertEquals($isCaseSensitive, Symfony_Component_Finder_Expression_Expression::create($expr)->isCaseSensitive());
    }

    /**
     * @dataProvider getRegexRenderingData
     */
    public function testRegexRendering($expr, $body)
    {
        $this->assertEquals($body, Symfony_Component_Finder_Expression_Expression::create($expr)->renderPattern());
    }

    public function getTypeGuesserData()
    {
        return array(
            array('{foo}', Symfony_Component_Finder_Expression_Expression::TYPE_REGEX),
            array('/foo/', Symfony_Component_Finder_Expression_Expression::TYPE_REGEX),
            array('foo',   Symfony_Component_Finder_Expression_Expression::TYPE_GLOB),
            array('foo*',  Symfony_Component_Finder_Expression_Expression::TYPE_GLOB),
        );
    }

    public function getCaseSensitiveData()
    {
        return array(
            array('{foo}m', true),
            array('/foo/i', false),
            array('foo*',   true),
        );
    }

    public function getRegexRenderingData()
    {
        return array(
            array('{foo}m', 'foo'),
            array('/foo/i', 'foo'),
        );
    }
}
