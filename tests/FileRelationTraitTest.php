<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;


use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\File\Tests\Testclasses\File;
use PhilippR\Atk4\File\Tests\Testclasses\UploadedFileMock;
use PhilippR\Atk4\File\Tests\Testclasses\ModelWithFileRelation;


class FileRelationTraitTest extends TestCase
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
        $m = new ModelWithFileRelation($this->persistence, ['fileClassName' => FileMock::class]);
        $m->save();
        $m->addUploadFileFromAtkUi('error');
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m = new ModelWithFileRelation($this->persistence, ['fileClassName' => FileMock::class]);
        self::assertEquals(null, $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']));
    }

    public function testRemoveFile()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $f = $this->createTestFile('Hansi', $this->persistence, $m);
        self::assertEquals($m->ref(File::class)->action('count')->getOne(), 1);
        $m->removeFile($f->get('id'));
        self::assertEquals($m->ref(File::class)->action('count')->getOne(), 0);
    }
*/
    public function testExceptionNonExistingFile()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        self::expectException(UserException::class);
        $m->removeFile(23432543635);
    }
/*
    public function testaddUploadFileViaHookOnSave()
    {
        $m = new ModelWithFileRelation($this->persistence, ['fileClassName' => FileMock::class]);
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m->save();

        self::assertTrue(true);
    }

    public function testFilesAreDeletedOnModelDelete()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $this->createTestFile('somefile.jpg', $this->persistence, $m);
        $this->createTestFile('someotherfile.jpg', $this->persistence, $m);
        self::assertEquals(
            2,
            (new File($this->persistence))->action('count')->getOne()
        );

        $m->delete();

        self::assertEquals(
            0,
            (new File($this->persistence))->action('count')->getOne()
        );
    }
*/
    public function testaddUploadFileFromAtkUi()
    {
        $model = new ModelWithFileRelation($this->persistence, ['fileClassName' => UploadedFileMock::class]);
        $model->save();
        self::assertTrue($model->hasRef(UploadedFileMock::class));

        $file = $model->addUploadFileFromAtkUi(['name' => 'testfile.txt', 'path' => 'tests/']);

        self::assertInstanceOf(File::class, $file);
        self::assertEquals(1, $model->ref(UploadedFileMock::class)->action('count')->getOne());
    }

    public function testAddTypeToFile()
    {
        $model = new ModelWithFileRelation($this->persistence, ['fileClassName' => UploadedFileMock::class]);
        $model->save();
        $file = $model->addUploadFileFromAtkUi(['name' => 'testfile.txt', 'path' => 'tests/']);
        self::assertEquals('', $file->get('type'));

        $file = $model->addUploadFileFromAtkUi(['name' => 'testfile.txt', 'path' => 'tests/'], 'SOMETYPE');
        self::assertEquals('SOMETYPE', $file->get('type'));
    }

    public function testFileSeedIsUsed()
    {
        $model = new ModelWithFileRelation($this->persistence, ['fileClassName' => UploadedFileMock::class]);
        self::assertTrue($model->hasRef(UploadedFileMock::class));
        self::assertFalse($model->hasRef(File::class));
    }
}
