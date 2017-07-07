<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Resolver\Exception;

use PB\Bundle\SuluStorageBundle\Resolver\Exception\DirectoryNotExistException;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;

class DirectoryNotExistExceptionTest extends AbstractTests
{
    public function testConstruction()
    {
        $exception = new DirectoryNotExistException('/uploads/test.gif');

        $this->assertEquals('The path \'/uploads/test.gif\' not exist or is not writable.', $exception->getMessage());
    }
}