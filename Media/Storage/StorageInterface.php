<?php

namespace PB\Bundle\SuluStorageBundle\Media\Storage;

use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use Sulu\Bundle\MediaBundle\Media\Storage\StorageInterface as BaseStorageInterface;

/**
 * Interface for PB Sulu storage implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface StorageInterface extends BaseStorageInterface
{
    /**
     * Get filesystem provider.
     *
     * @return FilesystemProviderInterface
     */
    public function getFilesystemProvider();
}
