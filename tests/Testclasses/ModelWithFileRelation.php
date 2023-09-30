<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

use Atk4\Data\Model;
use PhilippR\Atk4\File\FileRelationTrait;

class ModelWithFileRelation extends Model
{

    use FileRelationTrait;

    public $table = 'model_with_file_relation';

    protected function init(): void
    {
        parent::init();
        $this->addField('name');

        $this->addFileReferenceAndDeleteHook();
    }
}