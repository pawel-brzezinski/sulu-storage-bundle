<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Controller;

use PB\Bundle\SuluStorageBundle\Controller\MediaStreamController;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaStreamControllerTest extends WebTestCase
{
    public function testGetFileResponse()
    {
        $controller = new MediaStreamController();
        $storageOptions = [
            'segment' => '01',
            'fileName' => 'test.jpg'
        ];
        $fileVersion = new FileVersion();
        $fileVersion
            ->setName($storageOptions['fileName'])
            ->setVersion(1)
            ->setSize(10000)
            ->setStorageOptions(json_encode($storageOptions))
            ->setMimeType('image/jpeg');

        $reflection = new \ReflectionClass(MediaStreamController::class);
        $method = $reflection->getMethod('getFileResponse');
        $method->setAccessible(true);

        $result = $method->invokeArgs($controller, [$fileVersion, 'en', ResponseHeaderBag::DISPOSITION_ATTACHMENT]);


//        $this->assertTrue(false);
    }
}