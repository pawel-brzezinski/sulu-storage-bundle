<?php

namespace PB\Bundle\SuluStorageBundle\Media\Storage;

use PB\Bundle\SuluStorageBundle\Media\Resolver\FileResolverInterface;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\Exception\FilesystemProviderInternalException;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use PB\Component\Overlay\File\FileOverlay;
use PB\Component\Overlay\Math\MathOverlay;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * PB Sulu storage.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class PBStorage implements StorageInterface
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
     * @var FileOverlay
     */
    private $fileOverlay;

    /**
     * @var MathOverlay
     */
    private $mathOverlay;

    /**
     * @var int
     */
    private $segments;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FlysystemStorage constructor.
     *
     * @param FilesystemProviderInterface $filesystemProvider
     * @param FileResolverInterface $fileResolver
     * @param FileOverlay $fileOverlay
     * @param MathOverlay $mathOverlay
     * @param int|null $segments
     * @param LoggerInterface $logger
     */
    public function __construct(
        FilesystemProviderInterface $filesystemProvider,
        FileResolverInterface $fileResolver,
        FileOverlay $fileOverlay,
        MathOverlay $mathOverlay,
        $segments,
        LoggerInterface $logger = null
    ) {
        $this->filesystemProvider = $filesystemProvider;
        $this->fileResolver = $fileResolver;
        $this->fileOverlay = $fileOverlay;
        $this->mathOverlay = $mathOverlay;
        $this->segments = (int) $segments;
        $this->logger = null !== $logger ? $logger : new NullLogger();
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
     *
     * @throws FilesystemProviderInternalException
     */
    public function save($tempPath, $fileName, $version, $storageConfig = null)
    {
        $storageConfig = null === $storageConfig ? new StorageConfig() : new StorageConfig(json_decode($storageConfig, true));
        $segment = $storageConfig->get(StorageConfigInterface::SEGMENT_OPTION);

        if (null === $segment) {
            $segment = $this->getSegment($this->mathOverlay->rand(1, $this->segments));
        }

        $segmentPath = '/'.$segment;
        $fileName = $this->fileResolver->resolveUniqueFileName($segmentPath, $fileName, 0);
        $filePath = $this->fileResolver->resolveFilePath($segmentPath, $fileName);

        $this->logger->debug(sprintf('Read file %s', $tempPath));
        $fileContent = $this->fileOverlay->fileGetContents($tempPath);

        $this->logger->debug(sprintf('Write file to %s', $filePath));

        try {
            $this->filesystemProvider->write($filePath, $fileContent);
        } catch (\Exception $exception) {
            $this->logger->error('Filesystem provider thrown an exception during write file', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);

            throw new FilesystemProviderInternalException($exception->getMessage(), $exception->getCode());
        }

        $storageConfig = new StorageConfig();
        $storageConfig
            ->setSegment($segment)
            ->setFileName($fileName)
            ->setContentPath($this->filesystemProvider->getPathToFileContent($filePath))
        ;

        return json_encode($storageConfig->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function load($fileName, $version, $storageOption)
    {
        $storageOption = new StorageConfig(json_decode($storageOption, true));

        if (!$contentPath = $storageOption->getContentPath()) {
            return false;
        }

        return $contentPath;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAsString($fileName, $version, $storageOption)
    {
        $storageOption = new StorageConfig(json_decode($storageOption, true));

        $segment = $storageOption->getSegment();
        $fileName = $storageOption->getFileName();

        if (!$segment || !$fileName) {
            return false;
        }

        $segmentPath = '/'.$this->getSegment($segment);
        $filePath = $this->fileResolver->resolveFilePath($segmentPath, $fileName);

        try {
            return $this->filesystemProvider->read($filePath);
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Original media at path "%s" not found', $filePath));
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($storageOption)
    {
        $storageOption = new StorageConfig(json_decode($storageOption, true));

        $segment = $storageOption->getSegment();
        $fileName = $storageOption->getFileName();

        if (!$segment || !$fileName) {
            return false;
        }

        $segmentPath = '/'.$this->getSegment($segment);
        $filePath = $this->fileResolver->resolveFilePath($segmentPath, $fileName);

        try {
            $this->filesystemProvider->delete($filePath);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Get segment.
     *
     * @param int $segment
     *
     * @return string
     */
    private function getSegment($segment)
    {
        return sprintf('%0'.strlen($this->segments).'d', $segment);
    }
}
