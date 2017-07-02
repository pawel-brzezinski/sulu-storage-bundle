<?php

namespace PB\Bundle\SuluStorageBundle\HttpFoundation;

use League\Flysystem\File as FlysystemFile;
use PB\Bundle\SuluStorageBundle\HttpFoundation\Exception\BadFileManagerInstanceException;
use PB\Bundle\SuluStorageBundle\Manager\FlysystemFileManagerInterface;
use Sulu\Component\PHPCR\PathCleanupInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Overwritten Symfony BinaryFileResponse with use Flysystem File instance instead standard Symfony file instance.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class BinaryFlysystemFileManagerResponse extends Response
{
    /**
     * @var bool
     */
    protected static $trustXSendfileTypeHeader = false;

    /**
     * @var FlysystemFileManagerInterface
     */
    protected $manager;

    /**
     * @var FlysystemFile
     */
    protected $file;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $maxlen;

    /**
     * @var bool
     */
    protected $deleteFileAfterSend = false;

    /**
     * @var string
     */
    protected $locale = 'en';

    /**
     * @var PathCleanupInterface
     */
    protected $pathCleaner;

    /**
     * {@inheritdoc}
     *
     * @param FlysystemFileManagerInterface $manager
     * @param int $status
     * @param array $headers
     * @param bool $public
     * @param null $contentDisposition
     * @param bool $autoEtag
     * @param bool $autoLastModified
     * @param null|PathCleanupInterface $pathCleaner
     * @param string $locale
     */
    public function __construct(
        $manager,
        $status = 200,
        array $headers = array(),
        $public = true,
        $contentDisposition = null,
        $autoEtag = false,
        $autoLastModified = true,
        PathCleanupInterface $pathCleaner = null,
        $locale = 'en'
    ) {
        parent::__construct(null, $status, $headers);

        if (null !== $pathCleaner) {
            $this->setPathCleaner($pathCleaner, $locale);
        }

        $this->setFile($manager, $contentDisposition, $autoEtag, $autoLastModified);

        if ($public) {
            $this->setPublic();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param null|FlysystemFileManagerInterface $manager
     * @param int $status
     * @param array $headers
     * @param bool $public
     * @param null $contentDisposition
     * @param bool $autoEtag
     * @param bool $autoLastModified
     * @param PathCleanupInterface|null $pathCleaner
     * @param string $locale
     *
     * @return static
     */
    public static function create(
        $manager = null,
        $status = 200,
        $headers = array(),
        $public = true,
        $contentDisposition = null,
        $autoEtag = false,
        $autoLastModified = true,
        PathCleanupInterface $pathCleaner = null,
        $locale = 'en'
    ) {
        return new static($manager, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified, $pathCleaner, $locale);
    }

    /**
     * Sets the file to stream.
     *
     * @param FlysystemFileManagerInterface $manager
     * @param null|string $contentDisposition
     * @param bool $autoEtag
     * @param bool $autoLastModified
     *
     * @return $this
     *
     * @throws BadFileManagerInstanceException
     */
    public function setFile(
        $manager, $contentDisposition = null, $autoEtag = false, $autoLastModified = true)
    {
        if (!$manager instanceof FlysystemFileManagerInterface) {
            throw new BadFileManagerInstanceException($manager);
        }

        $this->manager = $manager;
        $this->file = $manager->getFile();

        if ($autoEtag) {
            $this->setAutoEtag();
        }

        if ($autoLastModified) {
            $this->setAutoLastModified();
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }

        return $this;
    }

    /**
     * Gets the file.
     *
     * @return FlysystemFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set locale.
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale ? $locale : 'en';
        return $this;
    }

    /**
     * Set path cleaner.
     *
     * @param PathCleanupInterface $cleaner
     * @param string $locale
     *
     * @return $this
     */
    public function setPathCleaner(PathCleanupInterface $cleaner, $locale)
    {
        $this->pathCleaner = $cleaner;
        $this->setLocale($locale);

        return $this;
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     *
     * @return $this
     */
    public function setAutoEtag()
    {
        $this->setEtag(sha1($this->file->read()));

        return $this;
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     *
     * @return $this
     */
    public function setAutoLastModified()
    {
        $timestamp = $this->file->getTimestamp();

        if (!$timestamp) {
            return $this;
        }

        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        $this->setLastModified($date);

        return $this;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition
     * @param string $filename
     * @param string $filenameFallback
     *
     * @return $this
     */
    public function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
    {
        $metadata = $this->file->getMetadata();

        if (!$filename && (!isset($metadata['basename']) || !$metadata['basename'])) {
            return $this;
        } elseif (!$filename) {
            $filename = $metadata['basename'];
        }

        if ('' === $filenameFallback && null !== $this->pathCleaner) {
            $pathInfo = pathinfo($filename);
            $filenameFallback = $this->pathCleaner->cleanup($pathInfo['filename'], $this->locale);
            $filenameFallback .= '.' . $pathInfo['extension'];
        }

        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);

        return $this;
    }

    /**
     * Prepare response.
     *
     * @param Request $request
     *
     * @return $this
     */
    public function prepare(Request $request)
    {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $this->file->getMimeType() ?: 'application/octet-stream');
        }

        if ('HTTP/1.0' !== $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->ensureIEOverSSLCompatibility($request);

        $this->offset = 0;
        $this->maxlen = -1;

        if (false === $fileSize = $this->file->getSize()) {
            return $this;
        }
        $this->headers->set('Content-Length', $fileSize);

        if (!$this->headers->has('Accept-Ranges')) {
            // Only accept ranges on safe HTTP methods
            $this->headers->set('Accept-Ranges', $request->isMethodSafe(false) ? 'bytes' : 'none');
        }

        if (self::$trustXSendfileTypeHeader && $request->headers->has('X-Sendfile-Type')) {
            // Use X-Sendfile, do not send any content.
            $type = $request->headers->get('X-Sendfile-Type');
            $path = $this->resolveFileUrlOrPath();

            if (strtolower($type) === 'x-accel-redirect') {
                // Do X-Accel-Mapping substitutions.
                // @link http://wiki.nginx.org/X-accel#X-Accel-Redirect
                foreach (explode(',', $request->headers->get('X-Accel-Mapping', '')) as $mapping) {
                    $mapping = explode('=', $mapping, 2);

                    if (2 === count($mapping)) {
                        $pathPrefix = trim($mapping[0]);
                        $location = trim($mapping[1]);

                        if (substr($path, 0, strlen($pathPrefix)) === $pathPrefix) {
                            $path = $location.substr($path, strlen($pathPrefix));
                            break;
                        }
                    }
                }
            }
            $this->headers->set($type, $path);
            $this->maxlen = 0;
        } elseif ($request->headers->has('Range')) {
            // Process the range headers.
            if (!$request->headers->has('If-Range') || $this->hasValidIfRangeHeader($request->headers->get('If-Range'))) {
                $range = $request->headers->get('Range');

                list($start, $end) = explode('-', substr($range, 6), 2) + array(0);

                $end = ('' === $end) ? $fileSize - 1 : (int) $end;

                if ('' === $start) {
                    $start = $fileSize - $end;
                    $end = $fileSize - 1;
                } else {
                    $start = (int) $start;
                }

                if ($start <= $end) {
                    if ($start < 0 || $end > $fileSize - 1) {
                        $this->setStatusCode(416);
                        $this->headers->set('Content-Range', sprintf('bytes */%s', $fileSize));
                    } elseif ($start !== 0 || $end !== $fileSize - 1) {
                        $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                        $this->offset = $start;

                        $this->setStatusCode(206);
                        $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                        $this->headers->set('Content-Length', $end - $start + 1);
                    }
                }
            }
        }

        return $this;
    }

    public function sendContent()
    {
        if (!$this->isSuccessful()) {
            return parent::sendContent();
        }

        if (0 === $this->maxlen) {
            return $this;
        }

        $out = fopen('php://output', 'wb');
        $file = $this->file->getFilesystem()->readStream($this->file->getPath());

        stream_copy_to_stream($file, $out, $this->maxlen, $this->offset);

        fclose($out);
        fclose($file);

        if ($this->deleteFileAfterSend) {
            $this->file->getFilesystem()->delete($this->file->getPath());
        }

        return $this;
    }

    /**
     * Trust X-Sendfile-Type header.
     */
    public static function trustXSendfileTypeHeader()
    {
        self::$trustXSendfileTypeHeader = true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException when the content is not null
     */
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a BinaryFileResponse instance.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return false
     */
    public function getContent()
    {
        return false;
    }

    /**
     * Resolve file full url or path.
     *
     * @return null|string
     */
    protected function resolveFileUrlOrPath()
    {
        $adapter = $this->file->getFilesystem()->getAdapter();

        if (null !== $extUrlManager = $this->manager->getExternalUrlResolver()) {
            return $extUrlManager->getUrl($adapter, $this->file->getPath());
        }

        return $this->manager->getPathResolver()->getFullPath($adapter, $this->file->getPath());
    }

    /**
     * Do request has valid If-Range header.
     *
     * @param string $header
     * @return bool
     */
    protected function hasValidIfRangeHeader($header)
    {
        if ($this->getEtag() === $header) {
            return true;
        }

        if (null === $lastModified = $this->getLastModified()) {
            return false;
        }

        return $lastModified->format('D, d M Y H:i:s').' GMT' === $header;
    }

    /**
     * If this is set to true, the file will be unlinked after the request is send
     * Note: If the X-Sendfile header is used, the deleteFileAfterSend setting will not be used.
     *
     * @param bool $shouldDelete
     *
     * @return $this
     */
    public function deleteFileAfterSend($shouldDelete)
    {
        $this->deleteFileAfterSend = $shouldDelete;

        return $this;
    }
}