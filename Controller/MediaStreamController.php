<?php

namespace PB\Bundle\SuluStorageBundle\Controller;

use Sulu\Bundle\MediaBundle\Controller\MediaStreamController as SuluMediaStreamController;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Overload Sulu MediaStreamController
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class MediaStreamController extends SuluMediaStreamController
{
    /**
     * Overloaded standard Sulu MediaStreamController::getFileResponse().
     *
     * @param FileVersion $fileVersion
     * @param string $locale
     * @param string $dispositionType
     *
     * @return Response
     */
    protected function getFileResponse(
        $fileVersion,
        $locale,
        $dispositionType = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    ) {
        $fileName = $fileVersion->getName();
        $fileSize = $fileVersion->getSize();
        $storageOptions = $fileVersion->getStorageOptions();
        $mimeType = $fileVersion->getMimeType();
        $version = $fileVersion->getVersion();
        $lastModified = $fileVersion->getCreated(); // use created as file itself is not changed when entity is changed

        $responseContent = $this->getStorage()->loadAsString($fileName, $version, $storageOptions);

        if (false === $responseContent) {
            return new Response('File not found', Response::HTTP_NOT_FOUND);
        }

        $response = new Response($responseContent);

        // Prepare headers
        $disposition = $response->headers->makeDisposition(
            $dispositionType,
            $fileName,
            $this->cleanUpFileName($fileName, $locale, $fileVersion->getExtension())
        );

        // Set headers for
        $file = $fileVersion->getFile();
        if ($fileVersion->getVersion() !== $file->getVersion()) {
            $latestFileVersion = $file->getLatestFileVersion();

            $response->headers->set(
                'Link',
                sprintf(
                    '<%s>; rel="canonical"',
                    $this->getMediaManager()->getUrl(
                        $file->getMedia()->getId(),
                        $latestFileVersion->getName(),
                        $latestFileVersion->getVersion()
                    )
                )
            );

            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        // Set headers
        $response->headers->set('Content-Type', !empty($mimeType) ? $mimeType : 'application/octet-stream');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-length', $fileSize);
        $response->headers->set('Last-Modified', $lastModified->format('D, d M Y H:i:s \G\M\T'));

        return $response;
    }

    /**
     * Overloaded standard Sulu MediaStreamController::cleanUpFileName().
     *
     * @param string $fileName
     * @param string $locale
     * @param string $extension
     *
     * @return string
     */
    private function cleanUpFileName($fileName, $locale, $extension)
    {
        $pathInfo = pathinfo($fileName);
        $cleanedFileName = $this->get('sulu.content.path_cleaner')->cleanup($pathInfo['filename'], $locale);

        if ($extension) {
            $cleanedFileName .= '.' . $extension;
        }

        return $cleanedFileName;
    }
}
