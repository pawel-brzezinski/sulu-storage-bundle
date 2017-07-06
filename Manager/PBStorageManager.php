<?php

namespace PB\Bundle\SuluStorageBundle\Manager;

use League\Flysystem\Filesystem;
use PB\Bundle\SuluStorageBundle\Resolver\UrlResolverInterface;
use PB\Bundle\SuluStorageBundle\Resolver\PathResolverInterface;

/**
 * PB Storage manager
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class PBStorageManager
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var PathResolverInterface
     */
    protected $pathResolver;

    /**
     * @var UrlResolverInterface
     */
    protected $urlResolver;

    /**
     * @var null|int
     */
    protected $segments = null;

    /**
     * PBStorageManager constructor.
     *
     * @param Filesystem $filesystem
     * @param PathResolverInterface $pathResolver
     * @param UrlResolverInterface $urlResolver
     * @param null|int $segments
     */
    public function __construct(
        Filesystem $filesystem,
        PathResolverInterface $pathResolver,
        UrlResolverInterface $urlResolver = null,
        $segments = null
    )
    {
        $this->filesystem = $filesystem;
        $this->pathResolver = $pathResolver;
        $this->urlResolver = $urlResolver;
        $this->segments = $segments;
    }

    /**
     * Get Flysystem filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Get path resolver.
     *
     * @return PathResolverInterface
     */
    public function getPathResolver()
    {
        return $this->pathResolver;
    }

    /**
     * Get url resolver.
     *
     * @return UrlResolverInterface
     */
    public function getUrlResolver()
    {
        return $this->urlResolver;
    }

    /**
     * Get media external url
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getUrl($fileName)
    {
        return $this->urlResolver->getUrl($this->filesystem->getAdapter(), $fileName);
    }

    /**
     * Get file path with folder.
     *
     * @param string $fileName
     * @param null|string $folder
     *
     * @return string
     */
    public function getFilePath($fileName, $folder = null)
    {
        return $folder === null ? $fileName : rtrim($folder, '/') . '/' . $fileName;
    }

    /**
     * Get format cache file path with folder.
     *
     * @param int $id
     * @param string $fileName
     * @param string $format
     * @param null|string $folder
     *
     * @return string
     */
    public function getFormatFilePath($id, $fileName, $format, $folder = null)
    {
        $fileName = $id . '-' . $fileName;

        if ($folder) {
            return $format . '/' . rtrim($folder, '/') . '/' . $fileName;
        }

        return $format . '/' . $fileName;
    }

    /**
     * Generate segment value.
     *
     * @return string
     */
    public function generateSegment()
    {
        if ($this->segments === null) {
            return null;
        }

        return sprintf('%0' . strlen($this->segments) . 'd', rand(1, $this->segments));
    }


    /**
     * Generate unique filename in filesystem path.
     * This method is based on original getUniqueFileName() method from Sulu LocalStorage class.
     *
     * @param string $fileName
     * @param string|null $folder
     * @param int $counter
     *
     * @return string
     */
    public function generateUniqueFileName($fileName, $folder = null, $counter = 0)
    {
        $newFileName = $fileName;

        if ($counter > 0) {
            $fileNameParts = explode('.', $fileName, 2);
            $newFileName = $fileNameParts[0] . '-' . $counter;

            if (isset($fileNameParts[1])) {
                $newFileName .= '.' . $fileNameParts[1];
            }
        }

        $filePath = $this->getFilePath($newFileName, $folder);

        if (!$this->filesystem->has($filePath)) {
            return $newFileName;
        }

        return $this->generateUniqueFileName($fileName, $folder, $counter + 1);
    }
}
