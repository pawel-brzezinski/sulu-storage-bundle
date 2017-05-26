<?php

namespace PB\Bundle\SuluStorageBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Overwritten Symfony BinaryFileResponse with use resource stream instead file instance
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class BinaryStreamResponse extends Response
{
    /**
     * @var bool
     */
    protected static $trustXSendfileTypeHeader = false;

    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var array
     */
    protected $streamMetaData;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $maxlen;

    /**
     * @var string
     */
    protected $mimeType = 'application/octet-stream';

    /**
     * @var int
     */
    protected $size;

    /**
     * BinaryStreamResponse constructor.
     *
     * @param mixed|resource $stream
     * @param int $status
     * @param array $headers
     * @param null|string $mimeType
     * @param bool $public
     */
    public function __construct($stream, $status = 200, $headers = [], $mimeType = null, $public = true)
    {
        parent::__construct(null, $status, $headers);

        $this->setStream($stream, $mimeType);

        if ($public) {
            $this->setPublic();
        }
    }

    /**
     * Create BinaryStreamResponse instance.
     *
     * @param null|resource $stream
     * @param int $status
     * @param array $headers
     * @param null|string $mimeType
     * @param bool $public
     *
     * @return BinaryStreamResponse
     */
    public static function create($stream = null, $status = 200, $headers = [], $mimeType = null, $public = true)
    {
        return new self($stream, $status, $headers, $mimeType, $public);
    }

    /**
     * Trust X-Sendfile-Type header.
     */
    public static function trustXSendfileTypeHeader()
    {
        self::$trustXSendfileTypeHeader = true;
    }

    /**
     * Set stream data.
     *
     * @param resource $stream
     * @param null|string $mimeType
     *
     * @return BinaryStreamResponse
     */
    public function setStream($stream, $mimeType = null)
    {
        $info = array_slice(fstat($stream), 13);

        $this->stream = $stream;
        $this->streamMetaData = stream_get_meta_data($stream);
        $this->mimeType = $mimeType ? $mimeType : 'application/octet-stream';
        $this->size = $info['size'];

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $request
     *
     * @return BinaryStreamResponse
     */
    public function prepare(Request $request)
    {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $this->mimeType);
        }

        if ('HTTP/1.0' !== $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->ensureIEOverSSLCompatibility($request);

        $this->offset = 0;
        $this->maxlen = -1;

        if (null === $this->size) {
            return $this;
        }

        $this->headers->set('Content-Length', $this->size);

        if (!$this->headers->has('Accept-Ranges')) {
            // Only accept ranges on safe HTTP methods
            $this->headers->set('Accept-Ranges', $request->isMethodSafe(false) ? 'bytes' : 'none');
        }

        if (isset($this->streamMetaData['uri']) &&
            $this->streamMetaData['uri'] &&
            self::$trustXSendfileTypeHeader &&
            $request->headers->has('X-Sendfile-Type')
        ) {
            // Use X-Sendfile, do not send any content.
            $type = $request->headers->get('X-Sendfile-Type');
            $path = $this->streamMetaData['uri'];

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
                $fileSize = $this->size;
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

    /**
     * {@inheritdoc}
     *
     * @return BinaryStreamResponse|Response
     */
    public function sendContent()
    {
        if (!$this->isSuccessful()) {
            return parent::sendContent();
        }

        if (0 === $this->maxlen) {
            return $this;
        }

        $out = fopen('php://output', 'wb');

        stream_copy_to_stream($this->stream, $out, $this->maxlen, $this->offset);
        fclose($out);
        fclose($this->stream);

        if (isset($this->streamMetaData['uri']) && $this->streamMetaData['uri']) {
            unlink($this->streamMetaData['uri']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException when the content is not null
     */
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a BinaryStreamResponse instance.');
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
     * @param $header
     * @return bool
     */
    private function hasValidIfRangeHeader($header)
    {
        if ($this->getEtag() === $header) {
            return true;
        }
        if (null === $lastModified = $this->getLastModified()) {
            return false;
        }
        return $lastModified->format('D, d M Y H:i:s').' GMT' === $header;
    }
}