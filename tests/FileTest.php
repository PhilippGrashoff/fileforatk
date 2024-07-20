<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;

use Atk4\Data\Exception;
use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\File\Tests\Testclasses\File;


class FileTest extends TestCase
{

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
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new File($this->db))->create();
    }

    public function testDelete(): void
    {
        $initial_file_count = (new File($this->db))->action('count')->getOne();
        //copy some file to use
        $f = (new File($this->db))->createEntity();
        $f->setFileName('filetest.txt');
        $this->copyFile($f->get('filename'));
        $f->save();
        self::assertTrue($f->checkFileExists());
        self::assertEquals(
            $initial_file_count + 1,
            (new File($this->db))->action('count')->getOne()
        );
        $cf = clone $f;
        $f->delete();
        self::assertFalse($cf->checkFileExists());
        self::assertEquals(
            $initial_file_count,
            (new File($this->db))->action('count')->getOne()
        );
    }

    public function testDeleteNonExistantFile(): void
    {
        $f = (new File($this->db))->createEntity();
        $f->set('filename', 'SomeNonExistantFile');
        self::assertFalse($f->deleteFile());
    }

    public function testExceptionOnSaveNonExistantFile(): void
    {
        $f = (new File($this->db))->createEntity();
        $f->set('filename', 'FDFLKSD LFSDHF KSJB');
        self::expectException(Exception::class);
        $f->save();
    }

    public function testCreateNewFileNameIfExists(): void
    {
        $f = (new File($this->db))->createEntity();
        $f1 = $this->createTestFile('LALA.jpg');
        $f->setFileName('LALA.jpg');
        self::assertNotEquals($f->get('filename'), $f1->get('filename'));
        self::assertEquals($f->get('filetype'), $f1->get('filetype'));
    }

    /*
    public function testSaveStringToFile()
    {
        $f = (new File($this->db))->createEntity();
        self::assertTrue($f->saveStringToFile('JLADHDDFEJD'));
    }*/

    public function testuploadFileUserExceptionOnError()
    {
        $f = (new File($this->db))->createEntity();
        //false because move_uploaded_file knows it's not an uploaded file
        self::expectException(UserException::class);
        $f->uploadFile(['name' => 'LALA', 'tmp_name' => 'sdfkjsdf.txt']);
    }

    public function testCryptId()
    {
        $file = $this->createTestFile('somefile.txt');
        self::assertEquals(21, strlen($file->get('crypt_id')));
    }

    public function testDirectorySeparatorAddedToPath()
    {
        $this->createTestFile('someotherfilename.txt');
        $file = (new File($this->db))->createEntity();
        $file->set('value', 'someotherfilename.txt');
        $file->set('path', 'tests/filedir');
        $file->save();
        self::assertEquals('tests/filedir/', $file->get('path'));
    }

    public function testFileTypeSetIfNotThere()
    {
        $this->createTestFile('evenanothername.txt');
        $g = (new File($this->db))->createEntity();
        $g->set('value', 'evenanothername.txt');
        $g->set('path', 'tests/filedir');
        $g->save();
        self::assertEquals('txt', $g->get('filetype'));
    }

    public function testNonExistantFileGetsDeletedOnUpdate()
    {
        $file = $this->createTestFile('somefile.jpg');
        $initial_file_count = (new File($this->db))->action('count')->getOne();
        unlink($file->getFullFilePath());
        $otherFile = (new File($this->db))->createEntity();
        $otherFile->load($file->get('id'));
        self::assertEquals(
            $initial_file_count - 1,
            (new File($this->db))->action('count')->getOne()
        );
    }

    protected function copyFile(string $filename, string $pathToFile = ''): bool
    {
        if (!$pathToFile) {
            $pathToFile = FILE_BASE_PATH . SAVE_FILES_IN;
        }
        return copy(
            $this->addDirectorySeperatorToPath(FILE_BASE_PATH) . 'tests/testfile.txt',
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
        $file = (new File($this->db))->createEntity();
        $file->setFileName($filename);
        $this->copyFile($file->get('filename'));
        $file->save();

        return $file;
    }

}
