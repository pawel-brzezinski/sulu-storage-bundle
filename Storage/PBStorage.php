<?php

namespace PB\Bundle\SuluStorageBundle\Storage;

class PBStorage
{
    /**
     * @var array
     */
    protected $filesystems = [];

    public function addFilesystem($name, $filesystem)
    {
        var_dump($filesystem);exit;
    }
}