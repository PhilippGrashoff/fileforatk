<?php

declare(strict_types=1);

namespace fileforatk;

use atk4\data\Model;
use atk4\data\Reference\HasMany;
use traitsforatkdata\UserException;

trait FileRelationTrait
{

    protected string $fileClassName = File::class;

    protected function addFileReferenceAndDeleteHook(bool $addDelete = true): HasMany
    {
        $ref = $this->hasMany(
            $this->fileClassName,
            [
                function () {
                    return (new $this->fileClassName($this->persistence, ['parentObject' => $this]))->addCondition(
                        'model_class',
                        get_class($this)
                    );
                },
                'their_field' => 'model_id'
            ]
        );

        if ($addDelete) {
            $this->onHook(
                Model::HOOK_AFTER_DELETE,
                function (self $model) {
                    foreach ($model->ref($this->fileClassName) as $file) {
                        $file->delete();
                    }
                }
            );
        }

        return $ref;
    }

    /**
     * Used to map ATK ui file input to data level
     */
    public function addUploadFileFromAtkUi($temp_file, string $type = ''): ?File
    {
        if ($temp_file === 'error') {
            return null;
        }

        //if $this was never saved (no id yet), use afterSave hook
        if (!$this->loaded()) {
            $this->onHook(
                Model::HOOK_AFTER_SAVE,
                function (self $model) use ($temp_file, $type) {
                    $this->_addUploadFile($temp_file, $type);
                }
            );
            return null;
        } //if id is available, do at once
        else {
            return $this->_addUploadFile($temp_file, $type);
        }
    }

    protected function _addUploadFile(array $temp_file, string $type): File
    {
        $file = new $this->fileClassName($this->persistence, ['parentObject' => $this]);
        if (!$file->uploadFile($temp_file)) {
            throw new UserException('Die Datei konnte nicht hochgeladen werden, bitte versuche es erneut');
        }
        if ($type) {
            $file->set('type', $type);
        }
        $file->save();

        return $file;
    }

    /**
     * removes a file reference.
     */
    public function removeFile($fileId)
    {
        $file = new $this->fileClassName($this->persistence);
        $file->tryLoad($fileId);
        if (!$file->loaded()) {
            throw new UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
        }

        $file->delete();
    }
}
