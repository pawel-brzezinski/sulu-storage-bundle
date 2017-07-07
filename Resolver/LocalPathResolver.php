<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Local;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\DirectoryNotExistException;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\WrongFlysystemAdapterException;

/**
 * Flysystem Local adapter path resolver
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class LocalPathResolver extends AbstractPathResolver
{
    /**
     * @var string
     */
    protected $absolutePathPrefix;

    /**
     * Set absolute path prefix.
     *
     * @param string $absolutePathPrefix
     *
     * @return $this
     */
    public function setAbsolutePathPrefix($absolutePathPrefix)
    {
        if (false === $realPath = realpath($absolutePathPrefix)) {
            throw new DirectoryNotExistException($absolutePathPrefix);
        }

        $this->absolutePathPrefix = $realPath;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return string
     */
    public function getRelativePath(AdapterInterface $adapter, $fileName)
    {
        $adapter = $this->extractAdapter($adapter);

        if (!$adapter instanceof Local) {
            throw new WrongFlysystemAdapterException(Local::class);
        }

        $fullPath = realpath($this->getFullPath($adapter, $fileName));

        return '/' . ltrim(str_replace($this->absolutePathPrefix, '', $fullPath), '/');
    }
}