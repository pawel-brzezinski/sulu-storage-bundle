<?php

namespace PB\Bundle\SuluStorageBundle\Controller;

use PB\Bundle\SuluStorageBundle\HttpFoundation\BinaryStreamResponse;
use PB\Bundle\SuluStorageBundle\Storage\PBStorageInterface;
use Sulu\Bundle\MediaBundle\Controller\MediaStreamController as SuluMediaStreamController;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Overload Sulu MediaStreamController
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class MediaStreamController extends SuluMediaStreamController
{
    /**
     * Overloaded standard Sulu Media getFileResponse with usage BinaryStreamResponse.
     *
     * @param FileVersion $fileVersion
     * @param string $locale
     * @param string $dispositionType
     * @return BinaryStreamResponse|RedirectResponse|Response
     */
    protected function getFileResponse(
        $fileVersion,
        $locale,
        $dispositionType = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    )
    {
        $cleaner = $this->get('sulu.content.path_cleaner');

        $fileName = $fileVersion->getName();
        $fileSize = $fileVersion->getSize();
        $storageOptions = $fileVersion->getStorageOptions();
        $mimeType = $fileVersion->getMimeType();
        $version = $fileVersion->getVersion();

        /** @var PBStorageInterface $storage */
        $storage = $this->getStorage();

        $mediaUrl = $storage->getMediaUrl($fileName, $storageOptions);

        if ($mediaUrl) {
            return new RedirectResponse($mediaUrl);
        }

        $stream = $storage->loadStream($fileName, $storageOptions);

        if (null === $stream) {
            return new Response('File not found', 404);
        }

        $response = new BinaryStreamResponse($stream);

        $pathInfo = pathinfo($fileName);

        // Prepare headers
        $disposition = $response->headers->makeDisposition(
            $dispositionType,
            $fileName,
            $cleaner->cleanup($pathInfo['filename'], $locale) . '.' . $pathInfo['extension']
        );

        // Set headers
        $response->headers->set('Content-Type', !empty($mimeType) ? $mimeType : 'application/octet-stream');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-length', $fileSize);

        return $response;
    }
}