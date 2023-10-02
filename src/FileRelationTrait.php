<?php

declare(strict_types=1);

namespace PhilippR\Atk4\File;

use Atk4\Data\Exception;
use atk4\data\Reference\HasMany;
use PhilippR\Atk4\SecondaryModel\SecondaryModelRelationTrait;

trait FileRelationTrait
{
    use SecondaryModelRelationTrait;

    protected string $fileClassName = File::class;

    /**
     * @param bool $addDelete
     * @return HasMany
     * @throws \Atk4\Core\Exception
     * @throws \Atk4\Data\Exception
     */
    protected function addFileReferenceAndDeleteHook(bool $addDelete = true): HasMany
    {
        return $this->addSecondaryModelHasMany($this->fileClassName, $addDelete);
    }

    /**
     *  Used to map ATK ui file input to data level TODO check if this is needed in V5 versions
     *
     * @param array $temp_file
     * @param string $type
     * @return File
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
     * @param $fileId
     * @return File
     */
    public function removeFile($fileId): File
    {
        $file = new $this->fileClassName($this->getPersistence());
        $file->load($fileId);
        $file->delete();
        return $file;
    }
}
