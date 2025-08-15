<?php declare(strict_types=1);

namespace PhilippR\Atk4\File;

use Atk4\Data\Exception;
use Atk4\Data\Model;

class FileController
{
    public static string $fileClass = File::class;

    public static function createFileEntityForFilename(
        Model  $parent,
        string $fileName,
        string $relativePath = '',
        array  $fieldValues = []
    ): File
    {
        $parent->assertIsEntity();
        $file = (new static::$fileClass($parent->getModel()->getPersistence()))->createEntity();
        $file->setParentEntity($parent);
        static::setRelativePath($file, $relativePath);
        static::setFileName($file, $fileName ?: 'UnnamedFile');
        static::setFieldValues($file, $fieldValues);

        return $file;
    }

    public static function saveStringToFile(
        string $stringToSave,
        Model  $parent,
        string $fileName,
        string $relativePath = '',
        array  $fieldValues = []
    ): File
    {
        $file = static::createFileEntityForFilename($parent, $fileName, $relativePath, $fieldValues);
        $result = file_put_contents($file->getFullFilePath(), $stringToSave);
        if ($result === false) {
            throw new Exception('Unable to write to file: ' . $file->getFullFilePath());
        }

        $file->save();
        return $file;
    }

    public static function createFileEntityForExistingFile(
        string $pathToFile,
        Model  $parent,
        array  $fieldValues = []
    ): File
    {
        $parent->assertIsEntity();
        $file = (new static::$fileClass($parent->getModel()->getPersistence()))->createEntity();
        $file->setParentEntity($parent);
        static::setRelativePath($file, static::getRelativePathFromPath($pathToFile));
        $file->set('filename', static::getFileNameFromPath($pathToFile));
        static::setFieldValues($file, $fieldValues);

        $file->save();
        return $file;
    }

    /**
     * @param array $tempFileData
     * @param Model $parent
     * @param string $relativePath
     * @param array $fieldValues
     * @return File
     * @throws Exception
     * @throws \Atk4\Core\Exception
     * @throws \Throwable
     */
    public static function saveUploadFileFromAtkUi(
        array  $tempFileData,
        Model  $parent,
        string $relativePath = '',
        array  $fieldValues = []
    ): File
    {
        $file = static::createFileEntityForFilename($parent, $tempFileData['name'], $relativePath, $fieldValues);
        try {
            move_uploaded_file($tempFileData['tmp_name'], $file->getFullFilePath());
        } catch (\Throwable $e) {
            throw new Exception('The file could not be uploaded.');
        }

        $file->save();
        return $file;
    }

    protected static function setFieldValues(File $file, array $fieldValues): void
    {
        foreach ($fieldValues as $fieldName => $value) {
            $file->set($fieldName, $value);
        }
    }

    public static function setRelativePath(File $file, string $relativePath): string
    {
        if (!$relativePath) {
            $relativePath = static::getDefaultRelativePath();
        }
        if (
            substr($relativePath, -1) !== DIRECTORY_SEPARATOR
            && strlen($relativePath) > 0
        ) {
            $relativePath .= DIRECTORY_SEPARATOR;
        }
        $file->set('relative_path', $relativePath);

        return (string)$file->get('relative_path');
    }

    public static function setFileName(File $file, string $name): void
    {
        $file->set('filename', SafeFileName::createSafeFileName($name));
        $file->set('filetype', pathinfo($name, PATHINFO_EXTENSION));

        $currentName = $file->get('filename');
        $i = 1;
        while (file_exists($file->getFullFilePath())) {
            $file->set(
                'filename',
                pathinfo($currentName, PATHINFO_FILENAME) . '_' . $i .
                ($file->get('filetype') ? '.' . $file->get('filetype') : '')
            );
            $i++;
        }
    }

    protected static function getRelativePathFromPath(string $pathToFile): string
    {
        $dir = pathinfo($pathToFile, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        return str_replace(static::getBaseDir(), '', $dir);
    }

    protected static function getFileNameFromPath(string $pathToFile): string
    {
        return pathinfo($pathToFile, PATHINFO_BASENAME);
    }

    public static function getBaseDir(): string
    {
        return '';
    }

    public static function getDefaultRelativePath(): string
    {
        return '';
    }
}