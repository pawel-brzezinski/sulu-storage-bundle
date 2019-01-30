<?php

namespace PB\Bundle\SuluStorageBundle\Provider\Filesystem;

use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

/**
 * Flysystem filesystem provider.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class FlysystemProvider implements FilesystemProviderInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * FlysystemProvider constructor.
     *
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($path)
    {
        return $this->filesystem->has($path);
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileExistsException
     */
    public function write($path, $contents, $config = null)
    {
        $config = is_array($config) ? $config : [];

        return $this->filesystem->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileNotFoundException
     */
    public function read($path)
    {
        return $this->filesystem->read($path);
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function move($source, $destination)
    {
        return $this->filesystem->rename($source, $destination);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return $this->filesystem->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($path)
    {
        return $this->filesystem->deleteDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPathToFileContent($path)
    {
        return $this->filesystem->getPathToFileContent($path);
    }
}
