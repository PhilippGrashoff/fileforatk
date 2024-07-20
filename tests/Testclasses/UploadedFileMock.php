<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

use Atk4\Data\Model;

class UploadedFileMock extends File
{

    public function saveUploadFileFromAtkUi(
        array $tempFileData,
        Model $parent,
        string $relativePath = '',
        array $fieldValues = []
    ): static {
        try {
            parent::saveUploadFileFromAtkUi(
                $tempFileData,
                $parent,
                $relativePath,
                $fieldValues
            );
        } catch (\Throwable $e) {
        }
        return $this;
    }
}
