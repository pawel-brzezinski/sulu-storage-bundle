<?php

namespace PB\Bundle\SuluStorageBundle\Config;

/**
 * Interface for config implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface ConfigInterface
{
    /**
     * Returns flag which determine whether config has requested option.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function has($key);

    /**
     * Get config option.
     *
     * @param string $key
     *
     * @return mixed|null       Returns null when key option does not exist
     */
    public function get($key);

    /**
     * Set config option.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function set($key, $value);

    /**
     * Dump config object to array.
     *
     * @return array
     */
    public function toArray();
}
