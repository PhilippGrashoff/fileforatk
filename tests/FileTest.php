<?php declare(strict_types=1);

namespace fileforatk\tests;

use fileforatk\File;
use traitsforatkdata\TestCase;


class FileTest extends TestCase
{

    private $persistence;

    protected $sqlitePersistenceModels = [
        File::class
    ];

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
        $f->createFileName('filetest.jpg');
        $this->copyFile($f->get('value'));
        $f->save();
        self::assertTrue($f->checkFileExists());
        $cf = clone $f;
        $f->delete();
        self::assertFalse($cf->checkFileExists());
        self::assertEquals($initial_file_count, (new File($this->persistence))->action('count')->getOne());
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
        self::expectException(\atk4\data\Exception::class);
        $f->save();
    }

    public function testCreateNewFileNameIfExists()
    {
        $f = new File($this->persistence);
        $f1 = $this->createTestFile('LALA.jpg', $this->persistence);
        $f->createFileName($f1->get('value'));
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
        $g = new File($this->persistence);
        $g->set('value', 'demo_file.txt');
        $g->set('path', 'tests/');
        $g->save();
        $c = $g->get('crypt_id');
        self::assertEquals(21, strlen($g->get('crypt_id')));

        //see if it stays the same after another save
        $g->save();
        self::assertEquals($c, $g->get('crypt_id'));
    }

    public function testDirectorySeparatorAddedToPath()
    {
        $g = new File($this->persistence);
        $g->set('value', 'demo_file.txt');
        $g->set('path', 'tests');
        $g->save();
        self::assertEquals('tests/', $g->get('path'));
    }

    public function testFileTypeSetIfNotThere()
    {
        $g = new File($this->persistence);
        $g->set('value', 'demo_file.txt');
        $g->set('path', 'tests');
        $g->save();
        self::assertEquals('txt', $g->get('filetype'));
    }

    public function testNonExistantFileGetsDeletedOnUpdate()
    {
        $file = $this->createTestFile('somefile.jpg', $this->persistence);
        $initial_file_count = (new File($this->persistence))->action('count')->getOne();
        unlink($file->getFullFilePath());
        $otherFile = new File($this->persistence);
        $otherFile->load($file->get('id'));
        self::assertEquals(
            $initial_file_count - 1,
            (new File($this->persistence))->action('count')->getOne()
        );
    }
}
