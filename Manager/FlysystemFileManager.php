<?php

namespace PB\Bundle\SuluStorageBundle\Manager;

use League\Flysystem\File;
use PB\Bundle\SuluStorageBundle\Resolver\PathResolverInterface;
use PB\Bundle\SuluStorageBundle\Resolver\UrlResolverInterface;

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
     * @var UrlResolverInterface
     */
    protected $urlResolver;

    /**
     * FlysystemFileManager constructor.
     *
     * @param File $file
     * @param PathResolverInterface $pathResolver
     * @param UrlResolverInterface $urlResolver
     */
    public function __construct(
        File $file,
        PathResolverInterface $pathResolver,
        UrlResolverInterface $urlResolver
    ) {
        $this->file = $file;
        $this->pathResolver = $pathResolver;
        $this->urlResolver = $urlResolver;
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
     * @return null|UrlResolverInterface
     */
    public function getUrlResolver()
    {
        return $this->urlResolver;
    }
}