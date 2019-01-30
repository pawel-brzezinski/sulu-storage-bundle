<?php

namespace PB\Bundle\SuluStorageBundle\Provider\Filesystem;

/**
 * Interface for filesystem provider implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface FilesystemProviderInterface
{
    /**
     * Returns flag which determine whether file exist in storage.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path);

    /**
     * Write file.
     *
     * @param string $path
     * @param string $contents
     * @param mixed $config
     *
     * @return bool
     */
    public function write($path, $contents, $config = null);

    /**
     * Read file.
     *
     * @param string $path
     *
     * @return string
     */
    public function read($path);

    /**
     * Move file from source to destination.
     *
     * @param string $source
     * @param string $destination
     *
     * @return bool
     */
    public function move($source, $destination);

    /**
     * Delete file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path);

    /**
     * Delete directory.
     *
     * @param string $path
     *
     * @return bool
     */
    public function deleteDir($path);

    /**
     * Get path to file content.
     *
     * @param string $path
     *
     * @return string
     */
    public function getPathToFileContent($path);
}
