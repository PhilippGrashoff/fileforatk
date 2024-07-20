<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

use Atk4\Data\Model;

class UploadedFileMock extends File
{

    public function saveUploadFileFromAtkUi(
        array $tempFileData,
        Model $parent,
        string $relativePath = '',
        string $type = ''
    ): static {

        try {
            parent::saveUploadFileFromAtkUi(
                $tempFileData,
                $parent,
                $relativePath,
                $type
            );
        }
        catch (\Exception $e) {

        }
        return $this;
    }
}
