<?php

declare(strict_types=1);

namespace PhilippR\Atk4\File;

use atk4\data\Reference\HasMany;
use PhilippR\Atk4\SecondaryModel\SecondaryModelRelationTrait;

trait FileRelationTrait
{
    use SecondaryModelRelationTrait;

    protected string $fileClassName = File::class;

    protected function addFileReferenceAndDeleteHook(bool $addDelete = true): HasMany
    {
        return $this->addSecondaryModelHasMany($this->fileClassName, $addDelete);
    }

    /**
     * Used to map ATK ui file input to data level
     */
    public function addUploadFileFromAtkUi(array $temp_file, string $type = ''): File
    {
        $file = (new $this->fileClassName($this->getPersistence()))->createEntity();
        $file->setParentEntity($this);
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
        $file = new $this->fileClassName($this->getPersistence());
        $file->tryLoad($fileId);
        if (!$file->loaded()) {
            throw new UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
        }

        $file->delete();
        return $file;
    }
}
