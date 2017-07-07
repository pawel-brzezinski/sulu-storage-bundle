<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver\Exception;

use PB\Bundle\SuluStorageBundle\Resolver\Exception\WrongFlysystemAdapterException;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class WrongFlysystemAdapterExceptionTest extends AbstractTests
{
    public function testConstruction()
    {
        $exception = new WrongFlysystemAdapterException('local');

        $this->assertEquals('Wrong Flysystem adapter has been passed. Required adapter: \'local\'.', $exception->getMessage());
    }
}