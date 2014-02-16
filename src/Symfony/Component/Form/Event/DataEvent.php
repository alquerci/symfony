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
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3. Code against
 *             {@link Symfony_Component_Form_FormEvent} instead.
 */
class Symfony_Component_Form_Event_DataEvent extends Symfony_Component_EventDispatcher_Event
{
    private $form;
    protected $data;

    /**
     * Constructs an event.
     *
     * @param Symfony_Component_Form_FormInterface $form The associated form
     * @param mixed         $data The data
     */
    public function __construct(Symfony_Component_Form_FormInterface $form, $data)
    {
        if (!$this instanceof Symfony_Component_Form_FormEvent) {
            version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error(sprintf('%s is deprecated since version 2.1 and will be removed in 2.3. Code against Symfony_Component_Form_FormEvent instead.', get_class($this)), E_USER_DEPRECATED);
        }

        $this->form = $form;
        $this->data = $data;
    }

    /**
     * Returns the form at the source of the event.
     *
     * @return Symfony_Component_Form_FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Returns the data associated with this event.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Allows updating with some filtered data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
