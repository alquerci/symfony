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
 * IdentityTranslator does not translate anything.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_Translation_IdentityTranslator implements Symfony_Component_Translation_TranslatorInterface
{
    private $selector;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Translation_MessageSelector $selector The message selector for pluralization
     *
     * @api
     */
    public function __construct(Symfony_Component_Translation_MessageSelector $selector)
    {
        $this->selector = $selector;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function setLocale($locale)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return strtr((string) is_object($id) && method_exists($id, '__toString') ? $id->__toString() : $id, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return strtr($this->selector->choose((string) is_object($id) && method_exists($id, '__toString') ? $id->__toString() : $id, (int) $number, $locale), $parameters);
    }
}
