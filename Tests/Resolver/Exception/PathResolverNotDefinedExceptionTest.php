<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver\Exception;

use PB\Bundle\SuluStorageBundle\Resolver\Exception\PathResolverNotDefinedException;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class PathResolverNotDefinedExceptionTest extends AbstractTests
{
    public function testConstruction()
    {
        $exception = new PathResolverNotDefinedException('local');

        $this->assertEquals('Path resolver for filesystem \'local\' is not defined.', $exception->getMessage());
    }
}