<?php

namespace PB\Bundle\SuluStorageBundle\Media\FormatManager;

use PB\Bundle\SuluStorageBundle\Media\FormatCache\FormatCacheInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;
use Sulu\Bundle\MediaBundle\Media\Exception\InvalidMimeTypeForPreviewException;
use Sulu\Bundle\MediaBundle\Media\Exception\MediaException;
use Sulu\Bundle\MediaBundle\Media\FormatManager\FormatManager as SuluFormatManager;
use Sulu\Bundle\MediaBundle\Media\ImageConverter\ImageConverterInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * PB Sulu format manager.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBFormatManager extends SuluFormatManager
{
    /**
     * The repository for communication with the database.
     *
     * @var MediaRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var FormatCacheInterface
     */
    private $formatCache;

    /**
     * @var ImageConverterInterface
     */
    private $converter;

    /**
     * @var bool
     */
    private $saveImage = false;

    /**
     * @var array
     */
    private $responseHeaders = [];

    /**
     * @var array
     */
    private $formats;

    /**
     * @var array
     */
    private $supportedMimeTypes;

    /**
     * @param MediaRepositoryInterface $mediaRepository
     * @param FormatCacheInterface $formatCache
     * @param ImageConverterInterface $converter
     * @param string $saveImage
     * @param array $responseHeaders
     * @param array $formats
     * @param array $supportedMimeTypes
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        MediaRepositoryInterface $mediaRepository,
        FormatCacheInterface $formatCache,
        ImageConverterInterface $converter,
        $saveImage,
        $responseHeaders,
        $formats,
        array $supportedMimeTypes,
        LoggerInterface $logger = null
    ) {
        parent::__construct($mediaRepository, $formatCache, $converter, $saveImage, $responseHeaders, $formats, $supportedMimeTypes);

        $this->mediaRepository = $mediaRepository;
        $this->formatCache = $formatCache;
        $this->converter = $converter;
        $this->saveImage = 'true' == $saveImage ? true : false;
        $this->responseHeaders = $responseHeaders;
        $this->formats = $formats;
        $this->supportedMimeTypes = $supportedMimeTypes;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function returnImage($id, $formatKey)
    {
        $setExpireHeaders = false;

        try {
            $media = $this->mediaRepository->findMediaByIdForRendering($id, $formatKey);

            if (!$media) {
                throw new ImageProxyMediaNotFoundException('Media was not found');
            }

            $fileVersion = $this->getLatestFileVersion($media);

            // Try to load existing image format
            $fileName = $this->replaceExtension($fileVersion->getName(), $fileVersion->getMimeType());
            $storageOptions = $fileVersion->getStorageOptions();
            $responseContent = $this->formatCache->load($id, $fileName, $storageOptions, $formatKey);

            if (null !== $responseContent) {
                $status = 200;
                $setExpireHeaders = true;

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($responseContent);
            } else {
                if (!$this->checkMimeTypeSupported($fileVersion->getMimeType())) {
                    throw new InvalidMimeTypeForPreviewException($fileVersion->getMimeType());
                }

                // Convert Media to format.
                $responseContent = $this->converter->convert($fileVersion, $formatKey);

                // HTTP Headers
                $status = 200;
                $setExpireHeaders = true;

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($responseContent);

                // Save image.
                if ($this->saveImage) {
                    $this->formatCache->save(
                        $responseContent,
                        $media->getId(),
                        $fileName,
                        $fileVersion->getStorageOptions(),
                        $formatKey
                    );
                }
            }
        } catch (MediaException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $responseContent = null;
            $status = 404;
            $mimeType = null;
        }

        // Set header.
        $headers = $this->getResponseHeaders($mimeType, $setExpireHeaders);

        // Return image.
        return new Response($responseContent, $status, $headers);
    }

    /**
     * Overload SuluFormatManager::getLatestFileVersion().
     *
     * @param MediaInterface $media
     *
     * @return FileVersion
     *
     * @throws ImageProxyMediaNotFoundException
     */
    private function getLatestFileVersion(MediaInterface $media)
    {
        foreach ($media->getFiles() as $file) {
            $version = $file->getVersion();
            foreach ($file->getFileVersions() as $fileVersion) {
                if ($fileVersion->getVersion() == $version) {
                    return $fileVersion;
                }
            }
            break;
        }

        throw new ImageProxyMediaNotFoundException('Media file version was not found');
    }

    /**
     * Overload SuluFormatManager::checkMimeTypeSupported().
     *
     * @param $mimeType
     *
     * @return bool
     */
    private function checkMimeTypeSupported($mimeType)
    {
        foreach ($this->supportedMimeTypes as $supportedMimeType) {
            if (fnmatch($supportedMimeType, $mimeType)) {
                return true;
            }
        }

        return false;
    }
}
