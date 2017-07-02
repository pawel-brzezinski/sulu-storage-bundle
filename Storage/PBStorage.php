<?php

namespace PB\Bundle\SuluStorageBundle\Storage;

use League\Flysystem\File;
use League\Flysystem\Filesystem;
use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManager;
use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;
use Sulu\Bundle\MediaBundle\Media\Storage\StorageInterface;

/**
 * Sulu media storage based on Flysystem
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class PBStorage implements StorageInterface, PBStorageInterface
{
    /**
     * @var PBStorageManager
     */
    protected $masterManager;

    /**
     * @var PBStorageManager
     */
    protected $replicaManager;

    /**
     * PBStorage constructor.
     *
     * @param PBStorageManager $masterManager
     * @param PBStorageManager|null $replicaManager
     */
    public function __construct(
        PBStorageManager $masterManager,
        PBStorageManager $replicaManager = null
    ) {
        $this->masterManager = $masterManager;
        $this->replicaManager = $replicaManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tempPath
     * @param string $fileName
     * @param int $version
     * @param string|null $storageOption
     *
     * @return string
     */
    public function save($tempPath, $fileName, $version, $storageOption = null)
    {
        $storageOption = $storageOption ? json_decode($storageOption) : new \stdClass();
        $segment = isset($storageOption->segment) ? $storageOption->segment : $this->masterManager->generateSegment();
        $fileName = $this->masterManager->generateUniqueFileName($fileName, $segment);

        $masterResult = $this->doSave($this->masterManager->getFilesystem(), $tempPath, $fileName, $segment, $storageOption);

        if ($this->replicaManager instanceof PBStorageManager) {
            $this->doSave($this->replicaManager->getFilesystem(), $tempPath, $fileName, $segment, $storageOption);
        }

        return $masterResult;
    }

    /**
     * {@inheritdoc}
     */
    public function load($fileName, $version, $storageOption)
    {
        throw new \RuntimeException('The \'load\' method is not supported by PBSuluStorageBundle.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadAsString($fileName, $version, $storageOption)
    {
        $filePath = $this->generateFilePath($fileName, $storageOption);

        if (!$this->masterManager->getFilesystem()->has($filePath)) {
            throw new ImageProxyMediaNotFoundException(sprintf('Original media at path "%s" not found', $filePath));
        }

        return $this->masterManager->getFilesystem()->read($filePath);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $storageOption
     *
     * @return bool
     */
    public function remove($storageOption)
    {
        $storageOption = $storageOption ? json_decode($storageOption) : new \stdClass();

        if (!isset($storageOption->fileName) || !$storageOption->fileName) {
            return false;
        }

        $filePath = $this->generateFilePath($storageOption->fileName, $storageOption);

        if (!$this->masterManager->getFilesystem()->has($filePath)) {
            return false;
        }

        $status = $this->masterManager->getFilesystem()->delete($filePath);

        if ($this->replicaManager instanceof PBStorageManager &&
            $this->replicaManager->getFilesystem()->has($filePath)
        ) {
            $this->replicaManager->getFilesystem()->delete($filePath);
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $fileName
     * @param null|string $storageOption
     *
     * @return null|string
     */
    public function getMediaUrl($fileName, $storageOption = null)
    {
        $filePath = $this->generateFilePath($fileName, $storageOption);

        return $this->masterManager->getUrl($filePath);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $fileName
     * @param null|string $storageOption
     *
     * @return null|false|resource
     */
    public function loadStream($fileName, $storageOption = null)
    {
        $filePath = $this->generateFilePath($fileName, $storageOption);

        if (!$this->masterManager->getFilesystem()->has($filePath)) {
            return null;
        }

        return $this->masterManager->getFilesystem()->readStream($filePath);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $fileName
     * @param null $storageOption
     * @return null|File
     */
    public function getFile($fileName, $storageOption = null)
    {
        $storageOption = $storageOption ? json_decode($storageOption) : new \stdClass();
        $segment = isset($storageOption->segment) ? $storageOption->segment : null;
        $filePath = $this->masterManager->getFilePath($fileName, $segment);

        if (!$this->masterManager->getFilesystem()->has($filePath)) {
            return null;
        }

        $file = $this->masterManager->getFilesystem()->get($filePath);

        return $file->isFile() ? $file : null;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $fileName
     * @param null $storageOption
     *
     * @return null|FlysystemFileManager
     */
    public function getFileManager($fileName, $storageOption = null)
    {
        $file = $this->getFile($fileName, $storageOption);

        if (null === $file) {
            return null;
        }

        return new FlysystemFileManager($file, $this->masterManager->getPathResolver(), $this->masterManager->getUrlResolver());
    }

    /**
     * Do save file using indicated filesystem.
     *
     * @param Filesystem $filesystem
     * @param string $tempPath
     * @param string $fileName
     * @param string $segment
     * @param null|string $storageOption
     *
     * @return string
     */
    protected function doSave(Filesystem $filesystem, $tempPath, $fileName, $segment, $storageOption = null)
    {
        $filePath = $segment . '/' . $fileName;

        $filesystem->write($filePath, file_get_contents($tempPath));

        return json_encode([
            'segment' => $segment,
            'fileName' => $fileName,
        ]);
    }

    /**
     * Generate file path by file name and storage options.
     *
     * @param string $fileName
     * @param null|string $storageOption
     *
     * @return string
     */
    protected function generateFilePath($fileName, $storageOption = null)
    {
        $storageOption = $storageOption ? json_decode($storageOption) : new \stdClass();
        $segment = isset($storageOption->segment) ? $storageOption->segment : null;

        return $this->masterManager->getFilePath($fileName, $segment);
    }
}