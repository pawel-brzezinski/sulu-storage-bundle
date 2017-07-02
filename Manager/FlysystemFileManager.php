<?php

namespace PB\Bundle\SuluStorageBundle\Manager;

use League\Flysystem\File;
use PB\Bundle\SuluStorageBundle\Resolver\PathResolverInterface;
use PB\Bundle\SuluStorageBundle\Resolver\ExternalUrlResolverInterface;

/**
 * Manager for Flysystem file item.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FlysystemFileManager implements FlysystemFileManagerInterface
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @var PathResolverInterface
     */
    protected $pathResolver;

    /**
     * @var ExternalUrlResolverInterface
     */
    protected $extUrlResolver;

    /**
     * FlysystemFileManager constructor.
     *
     * @param File $file
     * @param PathResolverInterface $pathResolver
     * @param ExternalUrlResolverInterface|null $externalUrlResolver
     */
    public function __construct(
        File $file,
        PathResolverInterface $pathResolver,
        ExternalUrlResolverInterface $externalUrlResolver = null
    ) {
        $this->file = $file;
        $this->pathResolver = $pathResolver;
        $this->extUrlResolver = $externalUrlResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     *
     * @return PathResolverInterface
     */
    public function getPathResolver()
    {
        return $this->pathResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|ExternalUrlResolverInterface
     */
    public function getExternalUrlResolver()
    {
        return $this->extUrlResolver;
    }
}