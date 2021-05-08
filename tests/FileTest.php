<?php declare(strict_types=1);

namespace fileforatk\tests;

use Atk4\Data\Exception;
use fileforatk\File;
use traitsforatkdata\TestCase;


class FileTest extends TestCase
{

    private $persistence;

    protected $sqlitePersistenceModels = [
        File::class
    ];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        define('FILE_BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
        define('SAVE_FILES_IN', 'filedir');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        foreach ((new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'filedir')) as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->persistence = $this->getSqliteTestPersistence();
    }

    public function testDelete()
    {
        $initial_file_count = (new File($this->persistence))->action('count')->getOne();
        //copy some file to use
        $f = new File($this->persistence);
        $f->createFileName('filetest.txt');
        $this->copyFile($f->get('value'));
        $f->save();
        self::assertTrue($f->checkFileExists());
        self::assertEquals(
            $initial_file_count + 1,
            (new File($this->persistence))->action('count')->getOne()
        );
        $cf = clone $f;
        $f->delete();
        self::assertFalse($cf->checkFileExists());
        self::assertEquals(
            $initial_file_count,
            (new File($this->persistence))->action('count')->getOne()
        );
    }

    public function testDeleteNonExistantFile()
    {
        $f = new File($this->persistence);
        $f->set('value', 'SomeNonExistantFile');
        self::assertFalse($f->deleteFile());
    }

    public function testExceptionOnSaveNonExistantFile()
    {
        $f = new File($this->persistence);
        $f->set('value', 'FDFLKSD LFSDHF KSJB');
        self::expectException(Exception::class);
        $f->save();
    }

    public function testCreateNewFileNameIfExists()
    {
        $f = new File($this->persistence);
        $f->set('path', SAVE_FILES_IN);
        $f1 = $this->createTestFile('LALA.jpg');
        $f->createFileName('LALA.jpg');
        self::assertNotEquals($f->get('value'), $f1->get('value'));
        self::assertEquals($f->get('filetype'), $f1->get('filetype'));
    }

    public function testSaveStringToFile()
    {
        $f = new File($this->persistence);
        self::assertTrue($f->saveStringToFile('JLADHDDFEJD'));
    }

    public function testuploadFile()
    {
        $f = new File($this->persistence);
        //false because move_uploaded_file knows its not an uploaded file
        self::assertFalse($f->uploadFile(['name' => 'LALA', 'tmp_name' => 'sdfkjsdf.txt']));
    }

    public function testCryptId()
    {
        $file = $this->createTestFile('somefile.txt');
        self::assertEquals(21, strlen($file->get('crypt_id')));
    }

    public function testDirectorySeparatorAddedToPath()
    {
        $this->createTestFile('someotherfilename.txt');
        $file = new File($this->persistence);
        $file->set('value', 'someotherfilename.txt');
        $file->set('path', 'filedir');
        $file->save();
        self::assertEquals('filedir/', $file->get('path'));
    }

    public function testFileTypeSetIfNotThere()
    {
        $this->createTestFile('evenanothername.txt');
        $g = new File($this->persistence);
        $g->set('value', 'evenanothername.txt');
        $g->set('path', 'filedir');
        $g->save();
        self::assertEquals('txt', $g->get('filetype'));
    }

    public function testNonExistantFileGetsDeletedOnUpdate()
    {
        $file = $this->createTestFile('somefile.jpg');
        $initial_file_count = (new File($this->persistence))->action('count')->getOne();
        unlink($file->getFullFilePath());
        $otherFile = new File($this->persistence);
        $otherFile->load($file->get('id'));
        self::assertEquals(
            $initial_file_count - 1,
            (new File($this->persistence))->action('count')->getOne()
        );
    }

    protected function copyFile(string $filename, string $pathToFile = ''): bool
    {
        if (!$pathToFile) {
            $pathToFile = FILE_BASE_PATH . SAVE_FILES_IN;
        }
        return copy(
            $this->addDirectorySeperatorToPath(FILE_BASE_PATH) . 'testfile.txt',
            $this->addDirectorySeperatorToPath($pathToFile) . $filename
        );
    }

    protected function addDirectorySeperatorToPath(string $path): string
    {
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            return $path . DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    public function createTestFile(
        string $filename
    ): File {
        $file = new File($this->persistence);
        $file->createFileName($filename);
        $this->copyFile($file->get('value'));
        $file->save();

        return $file;
    }

}
