<?php

namespace PB\Bundle\SuluStorageBundle\Storage;

use League\Flysystem\Filesystem;
use Sulu\Bundle\MediaBundle\Media\Storage\StorageInterface;

class PBStorage implements StorageInterface
{
    /**
     * @var Filesystem
     */
    protected $masterFilesystem;

    /**
     * @var Filesystem
     */
    protected $replicaFilesystem;

    /**
     * @var int
     */
    protected $segments = 10;

    public function setMasterFilesystem(Filesystem $filesystem)
    {
        $this->masterFilesystem = $filesystem;
        return $this;
    }

    public function setReplicaFilesystem(Filesystem $filesystem)
    {
        $this->replicaFilesystem = $filesystem;
        return $this;
    }

    public function setSegments($segments)
    {
        $this->segments = (int) $segments;
        return $this;
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
        $segment = isset($storageOption->segment) ? $storageOption->segment : $this->generateSegment($this->segments);
        $fileName = $this->generateUniqueFileName($this->masterFilesystem, $fileName, $segment);

        $masterResult = $this->doSave($this->masterFilesystem, $tempPath, $fileName, $segment, $storageOption);

        if ($this->replicaFilesystem instanceof Filesystem) {
            $this->doSave($this->replicaFilesystem, $tempPath, $fileName, $segment, $storageOption);
        }

        return $masterResult;
    }

    /**
     * {@inheritdoc}
     *
     * @param $fileName
     * @param $version
     * @param $storageOption
     *
     * @return string
     *
     * @deprecated Deprecated since 1.4, will be removed in 2.0
     */
    public function load($fileName, $version, $storageOption)
    {
        print_r('pbstorage load');exit;
        // TODO: Implement load() method.
    }

    /**
     * {@inheritdoc}
     *
     * @param $fileName
     * @param $version
     * @param $storageOption
     *
     * @return string
     */
    public function loadAsString($fileName, $version, $storageOption)
    {
        print_r('pbstorage loadAsString');exit;
        // TODO: Implement loadAsString() method.
    }

    /**
     * {@inheritdoc}
     *
     * @param $storageOption
     *
     * @return mixed
     */
    public function remove($storageOption)
    {
        print_r('pbstorage remove');exit;
        // TODO: Implement remove() method.
    }

    /**
     * Generate segment value.
     *
     * @param int $segments
     *
     * @return string
     */
    protected function generateSegment($segments)
    {
        return sprintf('%0' . strlen($segments) . 'd', rand(1, $segments));
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
     * Generate unique filename in filesystem path.
     * This method is based on original getUniqueFileName() method from Sulu LocalStorage class.
     *
     * @param Filesystem $filesystem
     * @param string $fileName
     * @param string|null $folder
     * @param int $counter
     *
     * @return string
     */
    protected function generateUniqueFileName(Filesystem $filesystem, $fileName, $folder = null, $counter = 0)
    {
        $newFileName = $fileName;

        if ($counter > 0) {
            $fileNameParts = explode('.', $fileName, 2);
            $newFileName = $fileNameParts[0] . '-' . $counter;

            if (isset($fileNameParts[1])) {
                $newFileName .= '.' . $fileNameParts[1];
            }
        }

        $filePath = rtrim($folder, '/') . '/' . $newFileName;

        if (!$filesystem->has($filePath)) {
            return $newFileName;
        }

        return $this->generateUniqueFileName($filesystem, $fileName, $folder, $counter + 1);
    }
}