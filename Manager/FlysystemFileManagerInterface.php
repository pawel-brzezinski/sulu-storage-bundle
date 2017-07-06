<?php

namespace PB\Bundle\SuluStorageBundle\Manager;

use League\Flysystem\File;
use PB\Bundle\SuluStorageBundle\Resolver\PathResolverInterface;
use PB\Bundle\SuluStorageBundle\Resolver\UrlResolverInterface;

/**
 * Interface for manager for Flysystem file item.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface FlysystemFileManagerInterface
{
    /**
     * Get Flysystem file instance.
     *
     * @return File
     */
    public function getFile();

    /**
     * Get path resolver.
     *
     * @return PathResolverInterface
     */
    public function getPathResolver();

    /**
     * Get external url resolver.
     *
     * @return null|UrlResolverInterface
     */
    public function getUrlResolver();
}