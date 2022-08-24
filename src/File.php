<?php declare(strict_types=1);

namespace fileforatk;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use secondarymodelforatk\SecondaryModel;
use traitsforatkdata\CryptIdTrait;


class File extends SecondaryModel
{
    use CryptIdTrait;

    public $table = 'file';


    protected function init(): void
    {
        parent::init();

        $this->addFileFields();

        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function (self $model, $isUpdate) {
                $this->addDirectorySeparatorToPath();
                //If file does not exist, dont save this in DB
                if (!$model->checkFileExists()) {
                    throw new Exception('The file to be saved does not exist: ' . $this->getFullFilePath());
                }

                //add filetype if not there
                if (
                    !$model->get('filetype')
                    && $model->get('value')
                ) {
                    $model->set('filetype', pathinfo($model->get('value'), PATHINFO_EXTENSION));
                }

                //file needs Crypt ID
                if (!$isUpdate) {
                    $model->setCryptId('crypt_id');
                }
            }
        );

        //if physical file does not exist any more, delete DB record, too
        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $model) {
                if ($model->checkFileExists()) {
                    return;
                }
                $model->delete();
                $model->breakHook(false);
            }
        );

        //after successful delete of DB record, delete physical file as well
        $this->onHook(
            Model::HOOK_AFTER_DELETE,
            function (self $model) {
                $model->deleteFile();
            }
        );

        //set path to standard if defined
        if (
            empty($this->get('path'))
            && defined('SAVE_FILES_IN')
        ) {
            $this->set('path', SAVE_FILES_IN);
            $this->addDirectorySeparatorToPath();
        }
    }

    protected function addFileFields(): void
    {
        //filename is stored in value
        //the relative path to the file project main dir, e.g. output/images/
        $this->addField(

            'path',
            [
                'type' => 'string'
            ]
        );

        //extra field to further classify file if needed, e.g. "ATTACHMENT", "TEMPORARY"
        $this->addField(
            'type',
            [
                'type' => 'string'
            ]
        );

        //pdf, jpg etc
        $this->addField(
            'filetype',
            [
                'type' => 'string'
            ]
        );

        //indicates if the file was generated by Script (true) or uploaded by user (false)
        $this->addField(
            'auto_generated',
            [
                'type' => 'boolean',
                'default' => false
            ]
        );

        //crypt_id, used when file is made available for download
        $this->addField(
            'crypt_id',
            [
                'type' => 'string',
                'system' => true
            ]
        );

        $this->addField(
            'sort',
            [
                'type' => 'string'
            ]
        );
    }

    protected function generateCryptId(): string
    {
        $return = '';
        for ($i = 0; $i < 21; $i++) {
            $return .= $this->getRandomChar();
        }

        return $return;
    }

    public function deleteFile(): bool
    {
        if (file_exists($this->getFullFilePath())) {
            return unlink($this->getFullFilePath());
        }
        return false;
    }

    public function createFileName(string $name, bool $uniqueName = true): void
    {
        $this->set('value', SafeFileName::createSafeFileName($name));
        $this->set('filetype', pathinfo($name, PATHINFO_EXTENSION));

        //can only check for existing file if path is set
        if (!$uniqueName) {
            return;
        }
        $currentName = $this->get('value');
        $i = 1;
        while (file_exists($this->getFullFilePath())) {
            $this->set(
                'value',
                pathinfo($currentName, PATHINFO_FILENAME) . '_' . $i .
                ($this->get('filetype') ? '.' . $this->get('filetype') : '')
            );
            $i++;
        }
    }

    /**
     * Uses $_FILES array content to call move_uploaded_file
     */
    public function uploadFile(array $f): bool
    {
        $this->createFileName($f['name']);
        //try move the uploaded file, quit on error
        return move_uploaded_file($f['tmp_name'], $this->getFullFilePath());
    }

    public function getFullFilePath(): string
    {
        return FILE_BASE_PATH . $this->addDirectorySeparatorToPath() . $this->get('value');
    }

    public function checkFileExists(): bool
    {
        return (
            file_exists($this->getFullFilePath())
            && is_file($this->getFullFilePath())
        );
    }

    public function saveStringToFile(string $string): bool
    {
        if (!$this->get('value')) {
            $this->createFileName('UnnamedFile');
        }
        return (bool)file_put_contents($this->getFullFilePath(), $string);
    }

    protected function addDirectorySeparatorToPath(): string
    {
        if (
            $this->get('path')
            && substr($this->get('path'), -1) !== DIRECTORY_SEPARATOR
        ) {
            $this->set('path', $this->get('path') . DIRECTORY_SEPARATOR);
        }

        return (string) $this->get('path');
    }
}
