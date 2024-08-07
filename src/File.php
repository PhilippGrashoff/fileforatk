<?php declare(strict_types=1);

namespace PhilippR\Atk4\File;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use PhilippR\Atk4\ModelTraits\CryptIdTrait;
use PhilippR\Atk4\SecondaryModel\SecondaryModel;


abstract class File extends SecondaryModel
{

    use CryptIdTrait;

    public $table = 'file';

    protected function init(): void
    {
        parent::init();
        $this->addFileFields();
        $this->addCryptIdFieldAndHooks('crypt_id');
        $this->addHooks();
    }

    protected function addFileFields(): void
    {
        //filename
        $this->addField('filename');

        //the relative path, usually to the file project main dir, e.g. output/images/
        $this->addField('relative_path');

        //pdf, jpg etc
        $this->addField('filetype');

        //extra field to further classify file if needed, e.g. "ATTACHMENT", "TEMPORARY"
        $this->addField('type');

        //can be used to indicate e.g. if the file was generated by script or uploaded by user. Define constants to your application's needs.
        $this->addField('origin');

        $this->addField('sort');
    }

    protected function addHooks(): void
    {
        //if physical file does not exist anymore, delete DB record, too
        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $fileEntity) {
                if ($fileEntity->checkFileExists()) {
                    return;
                }
                $fileEntity->delete();
                $fileEntity->breakHook(false);
            }
        );

        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function (self $fileEntity) {
                //If file does not exist, don't save this in DB
                if (!$fileEntity->checkFileExists()) {
                    throw new Exception('The file to be saved does not exist: ' . $this->getFullFilePath());
                }
            }
        );

        //after successful delete of DB record, delete physical file as well
        $this->onHook(
            Model::HOOK_AFTER_DELETE,
            function (self $fileEntity) {
                $fileEntity->deleteFile();
            }
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

    protected function deleteFile(): bool
    {
        if (file_exists($this->getFullFilePath())) {
            return unlink($this->getFullFilePath());
        }
        return false;
    }

    protected function setFileName(string $name): void
    {
        $this->set('filename', SafeFileName::createSafeFileName($name));
        $this->set('filetype', pathinfo($name, PATHINFO_EXTENSION));

        $currentName = $this->get('filename');
        $i = 1;
        while (file_exists($this->getFullFilePath())) {
            $this->set(
                'filename',
                pathinfo($currentName, PATHINFO_FILENAME) . '_' . $i .
                ($this->get('filetype') ? '.' . $this->get('filetype') : '')
            );
            $i++;
        }
    }

    public function getFullFilePath(): string
    {
        return $this->getBaseDir() . $this->get('relative_path') . $this->get('filename');
    }

    abstract public function getBaseDir(): string;

    abstract public function getDefaultRelativePath(): string;

    public function checkFileExists(): bool
    {
        return (
            file_exists($this->getFullFilePath())
            && is_file($this->getFullFilePath())
        );
    }

    public function saveStringToFile(
        string $stringToSave,
        Model $parent,
        string $fileName,
        string $relativePath = '',
        array $fieldValues = []
    ): static {
        $this->setParentEntity($parent);
        $this->setRelativePath($relativePath);
        $this->setFileName($fileName ?: 'UnnamedFile');
        $this->setFieldValues($fieldValues);

        $result = file_put_contents($this->getFullFilePath(), $stringToSave);
        if ($result === false) {
            throw new Exception('Unable to write to file: ' . $this->getFullFilePath());
        }

        $this->save();
        return $this;
    }

    /**
     * @param array $tempFileData
     * @param Model $parent
     * @param string $relativePath
     * @param string $type
     * @return File
     * @throws Exception
     * @throws \Atk4\Core\Exception
     */
    public function saveUploadFileFromAtkUi(
        array $tempFileData,
        Model $parent,
        string $relativePath = '',
        array $fieldValues = []
    ): static {
        $this->setParentEntity($parent);
        $this->setRelativePath($relativePath);
        $this->setFileName($tempFileData['name']);
        $this->setFieldValues($fieldValues);

        try {
            move_uploaded_file($tempFileData['tmp_name'], $this->getFullFilePath());
        } catch (\Throwable $e) {
            throw new Exception('The file could not be uploaded.');
        }

        $this->save();
        return $this;
    }

    protected function setFieldValues(array $fieldValues): void
    {
        foreach ($fieldValues as $fieldName => $value) {
            $this->set($fieldName, $value);
        }
    }

    protected function setRelativePath(string $relativePath): string
    {
        if (!$relativePath) {
            $relativePath = $this->getDefaultRelativePath();
        }
        if (substr($relativePath, -1) !== DIRECTORY_SEPARATOR) {
            $relativePath .= DIRECTORY_SEPARATOR;
        }
        $this->set('relative_path', $relativePath);

        return (string)$this->get('relative_path');
    }
}
