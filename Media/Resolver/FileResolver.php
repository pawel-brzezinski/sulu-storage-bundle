<?php

namespace PB\Bundle\SuluStorageBundle\Media\Resolver;

use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sulu\Component\PHPCR\PathCleanupInterface;

/**
 * File resolver.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FileResolver implements FileResolverInterface
{
    /**
     * @var FilesystemProviderInterface
     */
    private $filesystemProvider;

    /**
     * @var PathCleanupInterface
     */
    private $pathCleaner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FileResolver constructor.
     *
     * @param FilesystemProviderInterface $filesystemProvider
     * @param PathCleanupInterface $pathCleaner
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        FilesystemProviderInterface $filesystemProvider,
        PathCleanupInterface $pathCleaner,
        LoggerInterface $logger = null
    ) {
        $this->filesystemProvider = $filesystemProvider;
        $this->pathCleaner = $pathCleaner;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function resolveFilePath($folder, $fileName)
    {
        return rtrim($folder, '/').'/'.ltrim($fileName, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function resolveFileName($originalFileName)
    {
        $pathParts = pathinfo($originalFileName);
        $fileName = $this->pathCleaner->cleanup($pathParts['filename']);

        if (isset($pathParts['extension'])) {
            $fileName .= '.'.$pathParts['extension'];
        };

        return $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveUniqueFileName($folder, $fileName, $counter)
    {
        $resolvedFileName = $fileName;

        if (0 < $counter) {
            $pathParts = pathinfo($fileName);
            $resolvedFileName = $pathParts['filename'].'-'.$counter;

            if (isset($pathParts['extension'])) {
                $resolvedFileName .= '.'.$pathParts['extension'];
            }
        }

        $filePath = $this->resolveFilePath($folder, $resolvedFileName);

        $this->logger->debug('Check file name uniqueness: '.$filePath, [
            'folder' => $folder,
            'fileName' => $resolvedFileName,
            'counter' => $counter,
        ]);

        if (false === $this->filesystemProvider->exists($filePath)) {
            return $resolvedFileName;
        }

        return $this->resolveUniqueFileName($folder, $fileName, $counter + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveFormatFilePath($folder, $format, $fileName)
    {
        $path = $this->resolveFilePath($folder, $fileName);

        return '/'.ltrim($format, '/').'/'.ltrim($path, '/');
    }
}
