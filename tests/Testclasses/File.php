<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

class File extends \PhilippR\Atk4\File\File
{

    protected function getBaseDir(): string
    {
        return dirname(__DIR__) . '/';
    }

    protected function getDefaultRelativePath(): string
    {
        return 'filedir/';
    }
}