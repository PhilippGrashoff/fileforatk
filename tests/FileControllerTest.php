<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;


use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\File\Tests\Testclasses\File;
use PhilippR\Atk4\File\Tests\Testclasses\UploadedFileMock;
use PhilippR\Atk4\File\Tests\Testclasses\ModelWithFileRelation;


class FileControllerTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new File($this->db))->create();
        $this->createMigrator(new ModelWithFileRelation($this->db))->create();
    }

    /*
    public function testAddUploadedFile()
    {
        $m = new ModelWithFileRelation($this->db, ['fileClassName' => FileMock::class]);
        $m->save();
        $m->addUploadFileFromAtkUi('error');
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m = new ModelWithFileRelation($this->db, ['fileClassName' => FileMock::class]);
        self::assertEquals(null, $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']));
    }

    public function testRemoveFile()
    {
        $m = new ModelWithFileRelation($this->db);
        $m->save();
        $f = $this->createTestFile('Hansi', $this->db, $m);
        self::assertEquals($m->ref(File::class)->action('count')->getOne(), 1);
        $m->removeFile($f->get('id'));
        self::assertEquals($m->ref(File::class)->action('count')->getOne(), 0);
    }
*/
    public function testExceptionNonExistingFile()
    {
        $m = new ModelWithFileRelation($this->db);
        $m->save();
        self::expectException(UserException::class);
        $m->removeFile(23432543635);
    }
/*
    public function testaddUploadFileViaHookOnSave()
    {
        $m = new ModelWithFileRelation($this->db, ['fileClassName' => FileMock::class]);
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m->save();

        self::assertTrue(true);
    }

    public function testFilesAreDeletedOnModelDelete()
    {
        $m = new ModelWithFileRelation($this->db);
        $m->save();
        $this->createTestFile('somefile.jpg', $this->db, $m);
        $this->createTestFile('someotherfile.jpg', $this->db, $m);
        self::assertEquals(
            2,
            (new File($this->db))->action('count')->getOne()
        );

        $m->delete();

        self::assertEquals(
            0,
            (new File($this->db))->action('count')->getOne()
        );
    }
*/
    public function testaddUploadFileFromAtkUi()
    {
        $model = new ModelWithFileRelation($this->db, ['fileClassName' => UploadedFileMock::class]);
        $model->save();
        self::assertTrue($model->hasRef(UploadedFileMock::class));

        $file = $model->addUploadFileFromAtkUi(['name' => 'testfile.txt', 'path' => 'tests/']);

        self::assertInstanceOf(File::class, $file);
        self::assertEquals(1, $model->ref(UploadedFileMock::class)->action('count')->getOne());
    }

    public function testAddTypeToFile()
    {
        $model = new ModelWithFileRelation($this->db, ['fileClassName' => UploadedFileMock::class]);
        $model->save();
        $file = $model->addUploadFileFromAtkUi(['name' => 'testfile.txt', 'path' => 'tests/']);
        self::assertEquals('', $file->get('type'));

        $file = $model->addUploadFileFromAtkUi(['name' => 'testfile.txt', 'path' => 'tests/'], 'SOMETYPE');
        self::assertEquals('SOMETYPE', $file->get('type'));
    }

}
