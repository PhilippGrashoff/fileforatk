<?php

declare(strict_types=1);

namespace fileforatk;

use atk4\data\Model;
use atk4\data\Reference\HasMany;
use secondarymodelforatk\SecondaryModelRelationTrait;
use traitsforatkdata\UserException;

trait FileRelationTrait
{
    use SecondaryModelRelationTrait;

    protected string $fileClassName = File::class;

    protected function addFileReferenceAndDeleteHook(bool $addDelete = true): HasMany
    {
        return $this->addSecondaryModelHasMany($this->fileClassName);
    }

    /**
     * Used to map ATK ui file input to data level
     */
    public function addUploadFileFromAtkUi(array $temp_file, string $type = ''): File
    {
        $file = new $this->fileClassName($this->persistence, ['parentObject' => $this]);
        $file->uploadFile($temp_file);
        if ($type) {
            $file->set('type', $type);
        }
        $file->save();

        return $file;
    }

    /**
     * removes a file reference.
     */
    public function removeFile($fileId): File
    {
        $file = new $this->fileClassName($this->persistence);
        $file->tryLoad($fileId);
        if (!$file->loaded()) {
            throw new UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
        }

        $file->delete();
        return $file;
    }
}
