<?php

namespace PB\Bundle\SuluStorageBundle\Controller;

use PB\Bundle\SuluStorageBundle\HttpFoundation\BinaryFlysystemFileManagerResponse;
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
     * Overloaded standard Sulu Media getFileResponse with usage BinaryFlysystemFileManagerResponse.
     *
     * @param FileVersion $fileVersion
     * @param string $locale
     * @param string $dispositionType
     * @return BinaryFlysystemFileManagerResponse|RedirectResponse|Response
     */
    protected function getFileResponse(
        $fileVersion,
        $locale,
        $dispositionType = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    )
    {
        $cleaner = $this->get('sulu.content.path_cleaner');

        $fileName = $fileVersion->getName();
        $storageOptions = $fileVersion->getStorageOptions();

        /** @var PBStorageInterface $storage */
        $storage = $this->getStorage();

        $mediaUrl = $storage->getMediaUrl($fileName, $storageOptions);

        if ($mediaUrl) {
            return new RedirectResponse($mediaUrl);
        }

        $fileManager = $storage->getFileManager($fileName, $storageOptions);

        if (null === $fileManager) {
            return new Response('File not found', 404);
        }

        $response = new BinaryFlysystemFileManagerResponse(
            $fileManager,
            200,
            [],
            true,
            $dispositionType,
            false,
            true,
            $cleaner,
            $locale
        );

        return $response;
    }
}
