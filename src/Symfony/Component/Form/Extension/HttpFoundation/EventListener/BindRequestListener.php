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
 */
class Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // High priority in order to supersede other listeners
        return array(Symfony_Component_Form_FormEvents::PRE_BIND => array('preBind', 128));
    }

    public function preBind(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();

        /* @var Request $request */
        $request = $event->getData();

        // Only proceed if we actually deal with a Request
        if (!$request instanceof Symfony_Component_HttpFoundation_Request) {
            return;
        }

        $name = $form->getConfig()->getName();
        $default = $form->getConfig()->getCompound() ? array() : null;

        // Store the bound data in case of a post request
        switch ($request->getMethod()) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                if ('' === $name) {
                    // Form bound without name
                    $params = $request->request->all();
                    $files = $request->files->all();
                } else {
                    $params = $request->request->get($name, $default);
                    $files = $request->files->get($name, $default);
                }

                if (is_array($params) && is_array($files)) {
                    $data = $this->deepArrayUnion($params, $files);
                } else {
                    $data = $params ? $params : $files;
                }

                break;

            case 'GET':
                $data = '' === $name
                    ? $request->query->all()
                    : $request->query->get($name, $default);

                break;

            default:
                throw new Symfony_Component_Form_Exception_Exception(sprintf(
                    'The request method "%s" is not supported',
                    $request->getMethod()
                ));
        }

        $event->setData($data);
    }

    /**
     * Merges two arrays without reindexing numeric keys.
     *
     * @param array $array1 An array to merge
     * @param array $array2 An array to merge
     *
     * @return array The merged array
     */
    private function deepArrayUnion($array1, $array2)
    {
        if (function_exists('array_replace_recursive')) {
            return array_replace_recursive($array1, $array2);
        }

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = $this->deepArrayUnion($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }
}
