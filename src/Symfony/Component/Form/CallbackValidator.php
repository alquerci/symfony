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
 * Deprecated. You should use FormEvents::POST_BIND event listeners instead.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3.
 */
class Symfony_Component_Form_CallbackValidator implements Symfony_Component_Form_FormValidatorInterface
{
    private $callback;

    /**
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    public function __construct($callback)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('CallbackValidator is deprecated since version 2.1 and will be removed in 2.3. Use the FormEvents::POST_BIND event instead.', E_USER_DEPRECATED);

        $this->callback = $callback;
    }

    /**
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    public function validate(FormInterface $form)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('validate() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);

        return call_user_func($this->callback, $form);
    }
}
