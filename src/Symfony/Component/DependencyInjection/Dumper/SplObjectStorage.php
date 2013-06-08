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
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Symfony_Component_DependencyInjection_Dumper_SplObjectStorage implements ArrayAccess
{
    private $storage = array();
    private $metaDatas = array();

    /**
     * Adds an object inside the storage, and optionally associate it to some data.
     *
     * @param object $object The object to add.
     * @param mixed  $data   The data to associate with the object.
     */
    public function attach($object, $data = null)
    {
        if ($this->contains($object)) {
            return;
        }

        $key = $this->getHash($object);

        $this->storage[$key] = $object;
        $this->metaDatas[$key] = $data;
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
        if (false !== $key = array_search($object, $this->storage, true)) {
            return true;
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
            $this->storage[$key] = null;
            unset($this->storage[$key]);

            $this->metaDatas[$key] = null;
            unset($this->metaDatas[$key]);
        }
    }

    /**
     * @see SplObjectStorage::attach()
     */
    public function offsetSet($object, $data)
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
        if (false !== $key = array_search($object, $this->storage, true)) {
            return $this->metaDatas[$key];
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

    /**
     * Calculate a unique identifier for the contained objects
     *
     * @param object $object The object whose identifier is to be calculated.
     *
     * @return string A string with the calculated identifier.
     */
    protected function getHash($object)
    {
        do {
            $hash = sha1(uniqid(mt_rand(), true));
        } while (isset($this->storage[$hash]));

        return $hash;
    }
}
