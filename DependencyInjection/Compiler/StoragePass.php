<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler;

use League\Flysystem\FilesystemNotFoundException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to adapt Sulu storage implementation
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class StoragePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('pb_sulu_storage.filesystem.names') ||
            !$container->has('pb_sulu_storage.storage')
        ) {
            return;
        }

        foreach ($container->getParameter('pb_sulu_storage.filesystem.names') as $name) {
            $this->addFilesystemToStorage($container, $name);
        }



//        $fsNames = [
//            $container->getParameter('pb_sulu_storage.master.filesystem.name'),
//        ];
//
//        if ($container->getParameter('pb_sulu_storage.replica.filesystem.name') !== null) {
////            $fsNames =
//        }


        exit;
    }

    protected function addFilesystemToStorage(ContainerBuilder $container, $fsName)
    {
        $fsServiceName = 'oneup_flysystem.' . $fsName . '_filesystem';

        if (!$container->has($fsServiceName)) {
            throw new FilesystemNotFoundException(sprintf('Filesystem with name "%s" not found.', $fsName));
        }

//        $container->get('pb_sulu_storage.storage')
//            ->addFilesystem($fsName, $container->get($fsServiceName));
    }
}