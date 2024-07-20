<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

class File extends \PhilippR\Atk4\File\File
{

    public function getBaseDir(): string
    {
        return dirname(__DIR__) . '/';
    }

    public function getDefaultRelativePath(): string
    {
        return 'filedir/';
    }
}