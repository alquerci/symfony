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
 * TranslatorHelper.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Helper_TranslatorHelper extends Symfony_Component_Templating_Helper_Helper
{
    protected $translator;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Translation_TranslatorInterface $translator A TranslatorInterface instance
     */
    public function __construct(Symfony_Component_Translation_TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @see Symfony_Component_Translation_TranslatorInterface::trans()
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @see Symfony_Component_Translation_TranslatorInterface::transChoice()
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'translator';
    }
}
