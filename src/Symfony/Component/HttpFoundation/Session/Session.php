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
 * Session.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Drak <drak@zikula.org>
 *
 * @api
 */
class Symfony_Component_HttpFoundation_Session_Session implements Symfony_Component_HttpFoundation_Session_SessionInterface, IteratorAggregate, Countable
{
    /**
     * Storage driver.
     *
     * @var Symfony_Component_HttpFoundation_Session_Storage_SessionStorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    private $flashName;

    /**
     * @var string
     */
    private $attributeName;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpFoundation_Session_Storage_SessionStorageInterface $storage    A SessionStorageInterface instance.
     * @param Symfony_Component_HttpFoundation_Session_Attribute_AttributeBagInterface   $attributes An AttributeBagInterface instance, (defaults null for default AttributeBag)
     * @param Symfony_Component_HttpFoundation_Session_Flash_FlashBagInterface       $flashes    A FlashBagInterface instance (defaults null for default FlashBag)
     */
    public function __construct(Symfony_Component_HttpFoundation_Session_Storage_SessionStorageInterface $storage = null, Symfony_Component_HttpFoundation_Session_Attribute_AttributeBagInterface $attributes = null, Symfony_Component_HttpFoundation_Session_Flash_FlashBagInterface $flashes = null)
    {
        $this->storage = $storage ? $storage : new Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage();

        $attributes = $attributes ? $attributes : new Symfony_Component_HttpFoundation_Session_Attribute_AttributeBag();
        $this->attributeName = $attributes->getName();
        $this->registerBag($attributes);

        $flashes = $flashes ? $flashes : new Symfony_Component_HttpFoundation_Session_Flash_FlashBag();
        $this->flashName = $flashes->getName();
        $this->registerBag($flashes);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return $this->storage->start();
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->storage->getBag($this->attributeName)->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return $this->storage->getBag($this->attributeName)->get($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->storage->getBag($this->attributeName)->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->storage->getBag($this->attributeName)->all();
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        $this->storage->getBag($this->attributeName)->replace($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        return $this->storage->getBag($this->attributeName)->remove($name);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->storage->getBag($this->attributeName)->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->storage->isStarted();
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new ArrayIterator($this->storage->getBag($this->attributeName)->all());
    }

    /**
     * Returns the number of attributes.
     *
     * @return int The number of attributes
     */
    public function count()
    {
        return count($this->storage->getBag($this->attributeName)->all());
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($lifetime = null)
    {
        $this->storage->clear();

        return $this->migrate(true, $lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        return $this->storage->regenerate($destroy, $lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->storage->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->storage->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->storage->setId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->storage->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->storage->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataBag()
    {
        return $this->storage->getMetadataBag();
    }

    /**
     * {@inheritdoc}
     */
    public function registerBag(Symfony_Component_HttpFoundation_Session_SessionBagInterface $bag)
    {
        $this->storage->registerBag($bag);
    }

    /**
     * {@inheritdoc}
     */
    public function getBag($name)
    {
        return $this->storage->getBag($name);
    }

    /**
     * Gets the flashbag interface.
     *
     * @return Symfony_Component_HttpFoundation_Session_Flash_FlashBagInterface
     */
    public function getFlashBag()
    {
        return $this->getBag($this->flashName);
    }

    // the following methods are kept for compatibility with Symfony 2.0 (they will be removed for Symfony 2.3)

    /**
     * @return array
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function getFlashes()
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getFlashes() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        $all = $this->getBag($this->flashName)->all();

        $return = array();
        if ($all) {
            foreach ($all as $name => $array) {
                if (is_numeric(key($array))) {
                    $return[$name] = reset($array);
                } else {
                    $return[$name] = $array;
                }
            }
        }

        return $return;
    }

    /**
     * @param array $values
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function setFlashes($values)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('setFlashes() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        foreach ($values as $name => $value) {
            $this->getBag($this->flashName)->set($name, $value);
        }
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function getFlash($name, $default = null)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getFlash() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        $return = $this->getBag($this->flashName)->get($name);

        return empty($return) ? $default : reset($return);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function setFlash($name, $value)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('setFlash() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        $this->getBag($this->flashName)->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return Boolean
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function hasFlash($name)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('hasFlash() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        return $this->getBag($this->flashName)->has($name);
    }

    /**
     * @param string $name
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function removeFlash($name)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('removeFlash() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        $this->getBag($this->flashName)->get($name);
    }

    /**
     * @return array
     *
     * @deprecated since 2.1, will be removed from 2.3
     */
    public function clearFlashes()
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('clearFlashes() is deprecated since version 2.1 and will be removed in 2.3. Use the FlashBag instead.', E_USER_DEPRECATED);

        return $this->getBag($this->flashName)->clear();
    }
}
