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
 * PHP finder engine implementation.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class Symfony_Component_Finder_Adapter_PhpAdapter extends Symfony_Component_Finder_Adapter_AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function searchInDirectory($dir)
    {
        $flags = FilesystemIterator::SKIP_DOTS;

        if ($this->followLinks) {
            $flags |= FilesystemIterator::FOLLOW_SYMLINKS;
        }

        $iterator = new RecursiveIteratorIterator(
            new Symfony_Component_Finder_Iterator_RecursiveDirectoryIterator($dir, $flags),
            RecursiveIteratorIterator::SELF_FIRST
        );

        if ($this->minDepth > 0 || $this->maxDepth < PHP_INT_MAX) {
            $iterator = new Symfony_Component_Finder_Iterator_DepthRangeFilterIterator($iterator, $this->minDepth, $this->maxDepth);
        }

        if ($this->mode) {
            $iterator = new Symfony_Component_Finder_Iterator_FileTypeFilterIterator($iterator, $this->mode);
        }

        if ($this->exclude) {
            $iterator = new Symfony_Component_Finder_Iterator_ExcludeDirectoryFilterIterator($iterator, $this->exclude);
        }

        if ($this->names || $this->notNames) {
            $iterator = new Symfony_Component_Finder_Iterator_FilenameFilterIterator($iterator, $this->names, $this->notNames);
        }

        if ($this->contains || $this->notContains) {
            $iterator = new Symfony_Component_Finder_Iterator_FilecontentFilterIterator($iterator, $this->contains, $this->notContains);
        }

        if ($this->sizes) {
            $iterator = new Symfony_Component_Finder_Iterator_SizeRangeFilterIterator($iterator, $this->sizes);
        }

        if ($this->dates) {
            $iterator = new Symfony_Component_Finder_Iterator_DateRangeFilterIterator($iterator, $this->dates);
        }

        if ($this->filters) {
            $iterator = new Symfony_Component_Finder_Iterator_CustomFilterIterator($iterator, $this->filters);
        }

        if ($this->sort) {
            $iteratorAggregate = new Symfony_Component_Finder_Iterator_SortableIterator($iterator, $this->sort);
            $iterator = $iteratorAggregate->getIterator();
        }

        if ($this->paths || $this->notPaths) {
            $iterator = new Symfony_Component_Finder_Iterator_PathFilterIterator($iterator, $this->paths, $this->notPaths);
        }

        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'php';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeUsed()
    {
        return true;
    }
}
