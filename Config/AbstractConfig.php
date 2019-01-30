<?php

namespace PB\Bundle\SuluStorageBundle\Config;

/**
 * Abstract for config implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
abstract class AbstractConfig implements ConfigInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * AbstractConfig constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (false === $this->has($key)) {
            return null;
        }

        return $this->options[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->options;
    }
}
