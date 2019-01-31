<?php

namespace PB\Bundle\SuluStorageBundle\Media\Resolver;

/**
 * Interface for file resolver implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface FileResolverInterface
{
    /**
     * Resolve file path.
     *
     * @param string $folder
     * @param string $fileName
     *
     * @return string
     */
    public function resolveFilePath($folder, $fileName);

    /**
     * Returns file name without special characters and preserves file extension. Based on original Sulu method.
     *
     * @param string $originalFileName
     *
     * @return string
     */
    public function resolveFileName($originalFileName);

    /**
     * Resolve unique file name.
     *
     * @param string $folder
     * @param string $fileName
     * @param int $counter
     *
     * @return string
     */
    public function resolveUniqueFileName($folder, $fileName, $counter);

    /**
     * Resolve format file path.
     *
     * @param string $folder
     * @param string $format
     * @param string $fileName
     *
     * @return string
     */
    public function resolveFormatFilePath($folder, $format, $fileName);
}
