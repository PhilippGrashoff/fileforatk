<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;

use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\File\Tests\Testclasses\File;
use PhilippR\Atk4\File\Tests\Testclasses\FileController;
use PhilippR\Atk4\File\Tests\Testclasses\ModelWithFileRelation;


class FileControllerTest extends TestCase
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

    public function testCreateFileEntityForExistingFile(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $existingFilePath = FileController::getBaseDir() . FileController::getDefaultRelativePath() . 'testfile2.txt';
        file_put_contents($existingFilePath, 'sfsdfsdfs');
        FileController::createFileEntityForExistingFile($existingFilePath, $parent);
        self::assertEquals(1, $parent->ref(File::class)->action('count')->getOne());
    }

    public function testSaveStringToFileAddsToParent(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        FileController::saveStringToFile('test', $parent, 'testfile.txt');
        self::assertEquals(1, $parent->ref(File::class)->action('count')->getOne());
    }

    public function testSaveStringToFileSetsFieldValues(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $file = FileController::saveStringToFile(
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

    public function testFileTypeSetBySetFileName(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $file = FileController::saveStringToFile(
            'test',
            $parent,
            'testfile.txt',
            '',
            ['type' => 'TESTTYPE', 'sort' => 10, 'origin' => 'UPLOADED']
        );
        self::assertSame('txt', $file->get('filetype'));
    }

    public function testDirectorySeparatorIsAddedToRelativePath(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $file = FileController::saveStringToFile(
            'test',
            $parent,
            'testfile.txt',
            '',
            ['type' => 'TESTTYPE', 'sort' => 10, 'origin' => 'UPLOADED']
        );
        self::assertSame('filedir/', $file->get('relative_path'));
    }


    /*
    public function testSaveUploadFileFromAtkUiSetsFields(): void
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity()->save();
        $file->saveUploadFileFromAtkUi(
            ['name' => 'testfile.txt', 'path' => 'tests/'],
            $parent,
            '',
            ['type' => 'TESTTYPE', 'sort' => 10, 'origin' => 'UPLOADED']
        );

        self::assertSame('TESTTYPE', $file->get('type'));
        self::assertSame('10', $file->get('sort'));
        self::assertSame('UPLOADED', $file->get('origin'));
    }*/

    protected function createTestFile(string $filename): File
    {
        $parent = (new ModelWithFileRelation($this->db))->createEntity();
        $parent->save();

        $file = FileController::saveStringToFile('demostring', $parent, $filename);

        return $file;
    }
}
