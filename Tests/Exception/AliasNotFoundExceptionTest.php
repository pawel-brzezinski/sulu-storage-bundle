<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Exception;

use PB\Bundle\SuluStorageBundle\Exception\AliasNotFoundException;
use PHPUnit\Framework\TestCase;

class AliasNotFoundExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new AliasNotFoundException('foobar');

        $this->assertEquals('Tag \'foobar\' require an alias attribute.', $exception->getMessage());
    }
}