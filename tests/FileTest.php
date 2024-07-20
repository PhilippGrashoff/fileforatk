<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;

use Atk4\Data\Exception;
use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\File\Tests\Testclasses\File;
use PhilippR\Atk4\File\Tests\Testclasses\ModelWithFileRelation;
use PhilippR\Atk4\File\Tests\Testclasses\UploadedFileMock;


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

    public function testFileTypeSetBySetFileName(): void
    {
        $file = (new File($this->db))->createEntity();
        $helper = \Closure::bind(
            static function () use ($file) {
                $file->setFileName('evenanothername.txt');
            },
            null,
            $file
        );
        $helper();
        self::assertEquals('txt', $file->get('filetype'));
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

    public function testDirectorySeparatorIsAddedToRelativePath(): void
    {
        $file = (new File($this->db))->createEntity();
        $helper = \Closure::bind(
            static function () use ($file) {
                $file->setRelativePath('filedir');
            },
            null,
            $file
        );
        $helper();
        self::assertSame('filedir/', $file->get('relative_path'));
    }


    public function testSaveStringToFileAddsToParent(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $file = (new File($this->db))->createEntity();
        $file->saveStringToFile('test', $parent, 'testfile.txt');
        self::assertEquals(1, $parent->ref(File::class)->action('count')->getOne());
    }

    public function testSaveStringToFileSetsFieldValues(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $file = (new File($this->db))->createEntity();
        $file->saveStringToFile(
            'test',
            $parent,
            'testfile.txt',
            '',
            ['type' => 'TESTTYPE', 'sort' => 10, 'origin' => 'UPLOADED']
        );

        self::assertSame('TESTTYPE', $file->get('type'));
        self::assertSame('10', $file->get('sort'));
        self::assertSame('UPLOADED', $file->get('origin'));
    }

    public function testSaveUploadFileFromAtkUiSetsFields(): void
    {
        $parent = (new ModelWithFileRelation($this->db, ['fileClass' => UploadedFileMock::class]))->createEntity(
        )->save();
        $file = (new UploadedFileMock($this->db))->createEntity();
        $file->saveUploadFileFromAtkUi(
            ['name' => 'testfile.txt', 'path' => 'tests/'],
            $parent,
            '',
            ['type' => 'TESTTYPE', 'sort' => 10, 'origin' => 'UPLOADED']
        );

        self::assertSame('TESTTYPE', $file->get('type'));
        self::assertSame('10', $file->get('sort'));
        self::assertSame('UPLOADED', $file->get('origin'));
    }

    protected function createTestFile(string $filename): File
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity();
        $parent->save();
        $file = (new File($this->db))->createEntity();
        $file->saveStringToFile('demostring', $parent, $filename);

        return $file;
    }
}
