<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;

use Atk4\Data\Exception;
use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\File\Tests\Testclasses\File;
use PhilippR\Atk4\File\Tests\Testclasses\FileController;
use PhilippR\Atk4\File\Tests\Testclasses\ModelWithFileRelation;


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
        $this->createMigrator(new ModelWithFileRelation($this->db))->create();
    }

    public function testDelete(): void
    {
        $initial_file_count = (new File($this->db))->action('count')->getOne();
        //copy some file to use
        $f = $this->createTestFile('deleteTest.txt');
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

    public function testExceptionOnSaveNonExistentFile(): void
    {
        $f = (new File($this->db))->createEntity();
        $f->set('filename', 'FDFLKSD LFSDHF KSJB');
        self::expectException(Exception::class);
        $f->save();
    }

    public function testCreateNewFileNameIfExists(): void
    {
        $f1 = $this->createTestFile('LALA.jpg');
        $f2 = $this->createTestFile('LALA.jpg');
        self::assertNotEquals($f2->get('filename'), $f1->get('filename'));
        self::assertEquals($f2->get('filetype'), $f1->get('filetype'));
        self::assertSame('LALA_1.jpg', $f2->get('filename'));
    }

    public function testCryptId(): void
    {
        $file = $this->createTestFile('somefile.txt');
        self::assertEquals(21, strlen($file->get('crypt_id')));
    }


    public function testNonExistentFileGetsDeletedOnUpdate(): void
    {
        $file = $this->createTestFile('somefile.jpg');
        $file->reload(); //to get coverage for single line
        $initial_file_count = (new File($this->db))->action('count')->getOne();
        unlink($file->getFullFilePath());
        $otherFile = (new File($this->db))->load($file->getId());
        self::assertEquals(
            $initial_file_count - 1,
            (new File($this->db))->action('count')->getOne()
        );
    }


    public function testGetFullFilePath(): void
    {
        // Test with relative path
        $file = (new File($this->db))->createEntity();
        $file->set('relative_path', 'uploads/images/');
        $file->set('filename', 'test.jpg');

        $expectedRelativePath = FileController::getBaseDir() . 'uploads/images/test.jpg';
        self::assertEquals($expectedRelativePath, $file->getFullFilePath());

        // Test with absolute path (starting with /)
        $file->set('relative_path', '/var/www/files/');
        $file->set('filename', 'document.pdf');

        $expectedAbsolutePath = '/var/www/files/document.pdf';
        self::assertEquals($expectedAbsolutePath, $file->getFullFilePath());

        // Test with relative path without trailing slash
        $file->set('relative_path', 'temp');
        $file->set('filename', 'data.txt');

        $expectedPath = FileController::getBaseDir() . 'tempdata.txt';
        self::assertEquals($expectedPath, $file->getFullFilePath());

        // Test with empty relative path
        $file->set('relative_path', '');
        $file->set('filename', 'root.log');

        $expectedRootPath = FileController::getBaseDir() . 'root.log';
        self::assertEquals($expectedRootPath, $file->getFullFilePath());
    }

    protected function createTestFile(string $filename): File
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity();
        $parent->save();

        $file = FileController::saveStringToFile('demostring', $parent, $filename);

        return $file;
    }
}
