<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

class FileController extends \PhilippR\Atk4\File\FileController
{
    public static string $fileClass = File::class;

    public static function getBaseDir(): string
    {
        return dirname(__DIR__) . '/';
    }

    public static function getDefaultRelativePath(): string
    {
        return 'filedir/';
    }
}