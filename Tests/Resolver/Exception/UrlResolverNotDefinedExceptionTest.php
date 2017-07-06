<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver\Exception;

use PB\Bundle\SuluStorageBundle\Resolver\Exception\UrlResolverNotDefinedException;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class UrlResolverNotDefinedExceptionTest extends AbstractTests
{
    public function testConstruction()
    {
        $exception = new UrlResolverNotDefinedException('local');

        $this->assertEquals('Url resolver for filesystem \'local\' is not defined.', $exception->getMessage());
    }
}