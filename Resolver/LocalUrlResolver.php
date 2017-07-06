<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Local;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\WrongFlysystemAdapterException;

/**
 * Flysystem local adapter url resolver
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class LocalUrlResolver implements UrlResolverInterface
{
    /**
     * @var string
     */
    protected $webDir;

    /**
     * LocalUrlResolver constructor.
     *
     * @param string $webDir
     */
    public function __construct($webDir)
    {
        $this->webDir = $webDir;
    }

    /**
     * {@inheritdoc}
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return string
     *
     * @throws WrongFlysystemAdapterException
     */
    public function getUrl(AdapterInterface $adapter, $fileName)
    {
        if (!$adapter instanceof Local) {
            throw new WrongFlysystemAdapterException(Local::class);
        }

        return str_replace($this->webDir, '', realpath($adapter->applyPathPrefix($fileName)));
    }
}