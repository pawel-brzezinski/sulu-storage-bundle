<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Config;

use PB\Bundle\SuluStorageBundle\Config\AbstractConfig;
use PB\Bundle\SuluStorageBundle\Tests\Fake\Config\FakeConfig;
use PB\Bundle\SuluStorageBundle\Tests\Library\Utils\Reflection;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class AbstractConfigTest extends TestCase
{
    const DEFAULT_CONFIG_OPTIONS = ['foo' => 'bar', 'lorem' => 'ipsum', 'nullable' => null];

    /** @var AbstractConfig */
    private $configUnderTest;

    protected function setUp()
    {
        $this->configUnderTest = new FakeConfig(self::DEFAULT_CONFIG_OPTIONS);
    }

    protected function tearDown()
    {
        $this->configUnderTest = null;
    }

    public function constructDataProvider()
    {
        $options = ['a' => 'b'];

        return [
            'default options parameter' => [[], null],
            'custom options parameter' => [$options, $options],
        ];
    }

    /**
     * @dataProvider constructDataProvider
     *
     * @param array $expected
     * @param array|null $options
     *
     * @throws \ReflectionException
     */
    public function testConstruct(array $expected, array $options = null)
    {
        // Given
        $configUnderTest = null === $options ? new FakeConfig() :new FakeConfig($options);

        // When
        $actualOptions = Reflection::getPropertyValue($configUnderTest, 'options');

        // Then
        $this->assertSame($expected, $actualOptions);
    }

    public function hasDataProvider()
    {
        return [
            'check key which exist and is not null' => [true, 'foo'],
            'check key which exist and is null' => [true, 'nullable'],
            'check key which not exist' => [false, 'abc'],
        ];
    }

    /**
     * @dataProvider hasDataProvider
     *
     * @param $expected
     * @param $key
     */
    public function testHas($expected, $key)
    {
        // When
        $actual = $this->configUnderTest->has($key);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function getDataProvider()
    {
        return [
            'get key which exist' => ['bar', 'foo'],
            'get key which not exist' => [null, 'abc'],
        ];
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param $expected
     * @param $key
     */
    public function testGet($expected, $key)
    {
        // When
        $actual = $this->configUnderTest->get($key);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testSet()
    {
        // Given
        $expected = self::DEFAULT_CONFIG_OPTIONS;
        $expected['foo'] = 'foo-new';
        $expected['abc'] = 'def';

        // When
        $this->configUnderTest->set('foo', 'foo-new');
        $actualSetter = $this->configUnderTest->set('abc', 'def');
        $actualOptions = Reflection::getPropertyValue($this->configUnderTest, 'options');

        // Then
        $this->assertSame($this->configUnderTest, $actualSetter);
        $this->assertSame($expected, $actualOptions);
    }

    public function testToArray()
    {
        // Given
        $expected = self::DEFAULT_CONFIG_OPTIONS;

        // When
        $actual = $this->configUnderTest->toArray();

        // Then
        $this->assertSame($expected, $actual);
    }
}
