<?php

namespace PB\Bundle\SuluStorageBundle\Media\FormatCache;

use PB\Bundle\SuluStorageBundle\Media\Resolver\FileResolverInterface;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;
use Sulu\Bundle\MediaBundle\Media\FormatCache\LocalFormatCache;

/**
 * PB Sulu format cache.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class PBFormatCache extends LocalFormatCache implements FormatCacheInterface
{
    /**
     * @var FilesystemProviderInterface
     */
    private $filesystemProvider;

    /**
     * @var FileResolverInterface
     */
    private $fileResolver;

    /**
     * PBFormatCache constructor.
     *
     * @param FilesystemProviderInterface $filesystemProvider
     * @param FileResolverInterface $fileResolver
     * @param string $pathUrl
     * @param int $segments
     * @param array $formats
     */
    public function __construct(
        FilesystemProviderInterface $filesystemProvider,
        FileResolverInterface $fileResolver,
        $pathUrl,
        $segments,
        $formats
    ) {
        $this->filesystemProvider = $filesystemProvider;
        $this->fileResolver = $fileResolver;
        $this->pathUrl = $pathUrl;
        $this->segments = $segments;
        $this->formats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystemProvider()
    {
        return $this->filesystemProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id, $fileName, $options, $format)
    {
        $segmentPath = '/'.$this->getSegment($id);
        $fileName = $this->fileResolver->resolveFileName($fileName);
        $filePath = $this->fileResolver->resolveFormatFilePath($segmentPath, $format, $fileName);

        if (false === $this->filesystemProvider->exists($filePath)) {
            return null;
        }

        try {
            return  $this->filesystemProvider->read($filePath);
        } catch (\Exception $exception) {
            throw new ImageProxyMediaNotFoundException(sprintf('Format media at path "%s" not found', $filePath));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($content, $id, $fileName, $options, $format)
    {
        $segmentPath = '/'.$this->getSegment($id);
        $fileName = $this->fileResolver->resolveFileName($fileName);
        $filePath = $this->fileResolver->resolveFormatFilePath($segmentPath, $format, $fileName);

        try {
            $this->filesystemProvider->write($filePath, $content);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($id, $fileName, $options)
    {
        $segmentPath = '/'.$this->getSegment($id);
        $fileName = $this->fileResolver->resolveFileName($fileName);

        foreach ($this->formats as $format) {
            $filePath = $this->fileResolver->resolveFormatFilePath($segmentPath, $format['key'], $fileName);

            try {
                $this->filesystemProvider->delete($filePath);
            } catch (\Exception $exception) {
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach ($this->formats as $format) {
            $path = '/'.$format['key'];

            try {
                $this->filesystemProvider->deleteDir($path);
            } catch (\Exception $exception) {
            }
        }
    }
}
