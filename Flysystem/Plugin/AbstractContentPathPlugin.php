<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Plugin;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\InvalidAdapterException;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\InvalidFilesystemException;

/**
 * Abstract for path to file content plugin implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
abstract class AbstractContentPathPlugin implements PluginInterface
{
    const METHOD_NAME = 'getPathToFileContent';

    /**
     * Supported adapter.
     *
     * @var string
     */
    protected $supportedAdapter = NullAdapter::class;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * {@inheritdoc}
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        if (!$filesystem instanceof Filesystem) {
            throw new InvalidFilesystemException($filesystem);
        }

        $adapter = $filesystem->getAdapter();

        if (!$adapter instanceof $this->supportedAdapter) {
            throw new InvalidAdapterException(get_class($adapter), $this->supportedAdapter);
        }

        $this->filesystem = $filesystem;
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return self::METHOD_NAME;
    }
}
