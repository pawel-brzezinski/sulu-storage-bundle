<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Fake\Flysystem;

use League\Flysystem\FilesystemInterface;

/**
 * Fake Flysystem filesystem interface to mock plugins.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface FakeFilesystemInterface extends FilesystemInterface
{
    /**
     * Provide method from ContentPathPlugin.
     *
     * @param $path
     *
     * @return string
     */
    public function getPathToFileContent($path);
}
