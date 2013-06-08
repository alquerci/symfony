<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Symfony_Component_HttpKernel_HttpCache_SPLObjectStorage implements ArrayAccess
{
    private $storage = array();
    private $metaDatas   = array();

    /**
     * Adds an object inside the storage, and optionally associate it to some data.
     *
     * @param object $object The object to add.
     * @param mixed  $data   The data to associate with the object.
     */
    public function attach($object, $data = null)
    {
        foreach ($this->storage as $storeObject) {
            if ($object === $storeObject) {
                return;
            }
        }

        $this->storage[] = $object;
        $this->metaDatas[] = $data;
    }

    /**
     * Checks if the storage contains the object provided.
     *
     * @param object $object
     *
     * @return Boolean Returns TRUE if the object is in the storage, FALSE otherwise.
     */
    public function contains($object)
    {
        foreach ($this->storage as $storeObject) {
            if ($object === $storeObject) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes the object from the storage.
     *
     * @param object $object The object to remove.
     */
    public function detach($object)
    {
        if (false !== $key = array_search($object, $this->storage, true)) {
            unset($this->storage[$key]);
        }
    }

    /**
     * @see SplObjectStorage::attach()
     */
    public function offsetSet($object, $data = null)
    {
        $this->attach($object, $data);
    }

    /**
     * Returns the data associated with an object in the storage.
     *
     * @param object $object The object to look for.
     *
     * @return mixed The data previously associated with the object in the storage.
     *
     * @throws UnexpectedValueException when object could not be found.
     */
    public function offsetGet($object)
    {
        foreach ($this->storage as $key => $storeObject) {
            if ($object === $storeObject) {
                return $this->metaDatas[$key];
            }
        }

        throw new UnexpectedValueException('The object could not be found.');
    }

    /**
     * @see SplObjectStorage::contains()
     */
    public function offsetExists($object)
    {
        return $this->contains($object);
    }

    /**
     * @see SplObjectStorage::detach()
     */
    public function offsetUnset($object)
    {
        return $this->detach($object);
    }
}
