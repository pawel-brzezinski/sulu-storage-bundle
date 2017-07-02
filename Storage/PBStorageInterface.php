<?php

namespace PB\Bundle\SuluStorageBundle\Storage;

use League\Flysystem\File;
use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManagerInterface;

/**
 * Interface for PB storage
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
interface PBStorageInterface
{
    /**
     * Check if file exist in storage.
     *
     * @param string $fileName
     * @param null|string $storageOption
     * @return bool
     */
    public function isFileExist($fileName, $storageOption = null);

    /**
     * Get media external url.
     *
     * @param string $fileName
     * @param string|null $storageOption    JSON string
     *
     * @return string|null
     */
    public function getMediaUrl($fileName, $storageOption = null);

    /**
     * Load media stream.
     *
     * @param string $fileName
     * @param string|null $storageOption    JSON string
     *
     * @return null|false|resource
     */
    public function loadStream($fileName, $storageOption = null);

    /**
     * Get Flysystem File handler.
     *
     * @param string $fileName
     * @param string|null $storageOption
     *
     * @return null|File
     */
    public function getFile($fileName, $storageOption = null);

    /**
     * Get Filesystem File manager.
     *
     * @param string $fileName
     * @param string|null $storageOption
     *
     * @return null|FlysystemFileManagerInterface
     */
    public function getFileManager($fileName, $storageOption = null);
}