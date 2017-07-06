<?php

namespace PB\Bundle\SuluStorageBundle\FormatCache;

use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyInvalidUrl;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyUrlNotFoundException;
use Sulu\Bundle\MediaBundle\Media\FormatCache\FormatCacheInterface;

/**
 * Sulu media format cache based on Flysystem
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class PBFormatCache implements FormatCacheInterface
{
    /**
     * @var PBStorageManager
     */
    protected $storageManager;

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $proxyPath;

    /**
     * PBFormatCache constructor.
     *
     * @param PBStorageManager $manager
     * @param array $formats
     * @param string $proxyPath
     */
    public function __construct(PBStorageManager $manager, array $formats, $proxyPath)
    {
        $this->storageManager = $manager;
        $this->formats = $formats;
        $this->proxyPath = $proxyPath;
    }

    /**
     * Save image and return the url to the image.
     *
     * @param string $content
     * @param int $id
     * @param string $fileName
     * @param string $options
     * @param string $format
     *
     * @return bool
     */
    public function save($content, $id, $fileName, $options, $format)
    {
        $options = $options ? json_decode($options) : new \stdClass();
        $segment = isset($options->segment) ? $options->segment : null;

        $savePath = $this->storageManager->getFormatFilePath($id, $fileName, $format, $segment);

        try {
            $this->storageManager->getFilesystem()->write($savePath, $content);
        } catch (\Exception $ioException) {
            return false;
        }

        return true;
    }

    /**
     * Delete the image by the given parameters.
     *
     * @param int $id
     * @param string $fileName
     * @param string $options
     *
     * @return bool
     */
    public function purge($id, $fileName, $options)
    {
        $options = $options ? json_decode($options) : new \stdClass();
        $segment = isset($options->segment) ? $options->segment : null;

        foreach ($this->formats as $format) {
            $path = $this->storageManager->getFormatFilePath($id, $fileName, $format['key'], $segment);

            if ($this->storageManager->getFilesystem()->has($path)) {
                $this->storageManager->getFilesystem()->delete($path);
            }
        }

        return true;
    }

    /**
     * Return the url to an specific format of an media.
     *
     * @param int $id
     * @param string $fileName
     * @param string $options
     * @param string $format
     * @param int $version
     * @param int $subVersion
     *
     * @return string
     */
    public function getMediaUrl($id, $fileName, $options, $format, $version, $subVersion)
    {
        $options = $options ? json_decode($options) : new \stdClass();
        $segment = isset($options->segment) ? $options->segment : null;

        $filePath = $this->storageManager->getFormatFilePath($id, $fileName, $format, $segment);

        if (!$this->storageManager->getFilesystem()->has($filePath)) {
            return $this->getProxyPathUrl($id, $fileName, $format, $segment, $version, $subVersion);
        }

        $url = $this->storageManager->getUrl($filePath);
        $url .= '?v=' . $version . '-' . $subVersion;

        return $url;
    }

    /**
     * It is the copy of original method from SuluMediaBundle LocalFormatCache class.
     * 
     * Return the id and the format of a media.
     *
     * @param string $url
     *
     * @return array ($id, $format)
     *
     * @throws ImageProxyUrlNotFoundException
     */
    public function analyzedMediaUrl($url)
    {
        if (empty($url)) {
            throw new ImageProxyUrlNotFoundException('The given url was empty');
        }

        $id = $this->getIdFromUrl($url);
        $format = $this->getFormatFromUrl($url);

        return [$id, $format];
    }

    /**
     * Clears the format cache.
     */
    public function clear()
    {
        foreach ($this->formats as $format) {
            $this->storageManager->getFilesystem()->deleteDir($format['key']);
        }
    }

    /**
     * It is the copy of original method from SuluMediaBundle LocalFormatCache class.
     * 
     * Return the id of by a given url.
     *
     * @param string $url
     *
     * @return int
     *
     * @throws ImageProxyInvalidUrl
     */
    protected function getIdFromUrl($url)
    {
        $fileName = basename($url);
        $idParts = explode('-', $fileName);

        if (count($idParts) < 2) {
            throw new ImageProxyInvalidUrl('No `id` was found in the url');
        }

        $id = $idParts[0];

        if (preg_match('/[^0-9]/', $id)) {
            throw new ImageProxyInvalidUrl('The founded `id` was not a valid integer');
        }

        return $id;
    }

    /**
     * It is the copy of original method from SuluMediaBundle LocalFormatCache class.
     * 
     * Return the format by a given url.
     *
     * @param string $url
     *
     * @return string
     *
     * @throws ImageProxyInvalidUrl
     */
    protected function getFormatFromUrl($url)
    {
        $path = dirname($url);

        $formatParts = array_reverse(explode('/', $path));

        if (count($formatParts) < 2) {
            throw new ImageProxyInvalidUrl('No `format` was found in the url');
        }

        $format = $formatParts[1];

        return $format;
    }

    /**
     * Get proxy path url.
     *
     * @param int $id
     * @param string $fileName
     * @param string $format
     * @param null|string $folder
     * @param null|int $version
     * @param null|int $subVersion
     *
     * @return string
     */
    protected function getProxyPathUrl($id, $fileName, $format, $folder = null, $version = null, $subVersion = null)
    {
        $path = $format . '/';

        if ($folder) {
            $path .= $folder . '/';
        }

        $path .= $id . '-' . rawurlencode($fileName) . '?v=' . $version . '-' . $subVersion;

        return str_replace('{slug}', $path, $this->proxyPath);
    }
}