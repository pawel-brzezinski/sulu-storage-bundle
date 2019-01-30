<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Library\Utils;

/**
 * Static class with common reflection logic.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class Reflection
{
    /**
     * Calls static method and returns its result.
     *
     * @param string $class
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function callStaticMethod($class, $name, array $args)
    {
        $method = static::getReflectionMethod($class, $name);
        $method->setAccessible(true);

        return $method->invoke(null, ...$args);
    }

    /**
     * Returns property value of the given object.
     *
     * @param object $object
     * @param string $name
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function getPropertyValue($object, $name)
    {
        $property = static::getReflectionProperty(get_class($object), $name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Sets property value of the given object.
     *
     * @param object $object
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public static function setPropertyValue($object, $name, $value)
    {
        $property = static::getReflectionProperty(get_class($object), $name);
        $property->setAccessible(true);

        $property->setValue($object, $value);
    }

    /**
     * Returns new reflection of given class.
     *
     * @param string $class
     *
     * @return \ReflectionClass
     *
     * @throws \ReflectionException
     */
    public static function getReflectionClass($class)
    {
        return new \ReflectionClass($class);
    }

    /**
     * Returns method reflection of the given class.
     *
     * @param string $class
     * @param string $name
     *
     * @return \ReflectionMethod
     *
     * @throws \ReflectionException
     */
    public static function getReflectionMethod($class, $name)
    {
        return static::getReflectionClass($class)->getMethod($name);
    }

    /**
     * Returns property reflection of the given class.
     *
     * @param string $class
     * @param string $name
     *
     * @return \ReflectionProperty
     *
     * @throws \ReflectionException
     */
    public static function getReflectionProperty($class, $name)
    {
        return static::getReflectionClass($class)->getProperty($name);
    }
}
