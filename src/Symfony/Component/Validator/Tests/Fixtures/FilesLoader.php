<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Validator_Tests_Fixtures_FilesLoader extends Symfony_Component_Validator_Mapping_Loader_FilesLoader
{
    protected $timesCalled = 0;
    protected $loader;

    public function __construct(array $paths, Symfony_Component_Validator_Mapping_Loader_LoaderInterface $loader)
    {
        $this->loader = $loader;
        parent::__construct($paths);
    }

    protected function getFileLoaderInstance($file)
    {
        $this->timesCalled++;

        return $this->loader;
    }

    public function getTimesCalled()
    {
        return $this->timesCalled;
    }
}
