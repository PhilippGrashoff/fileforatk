<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

use Atk4\Data\Model;

class UploadedFileMockController extends FileController
{

    public static function saveUploadFileFromAtkUi(
        array $tempFileData,
        Model $parent,
        string $relativePath = '',
        array $fieldValues = []
    ): File {
        try {
            parent::saveUploadFileFromAtkUi(
                $tempFileData,
                $parent,
                $relativePath,
                $fieldValues
            );
        } catch (\Throwable $e) {
        }
        return (new File($parent->getModel()->getPersistence()))->createEntity();
    }
}
