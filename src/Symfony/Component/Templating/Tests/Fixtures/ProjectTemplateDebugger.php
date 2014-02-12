<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Fixtures_ProjectTemplateDebugger implements Symfony_Component_Templating_DebuggerInterface
{
    protected $messages = array();

    public function log($message)
    {
        $this->messages[] = $message;
    }

    public function hasMessage($regex)
    {
        foreach ($this->messages as $message) {
            if (preg_match('#'.preg_quote($regex, '#').'#', $message)) {
                return true;
            }
        }

        return false;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
