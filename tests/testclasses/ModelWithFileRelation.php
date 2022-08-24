<?php declare(strict_types=1);

namespace fileforatk\tests\testclasses;

use Atk4\Data\Model;
use fileforatk\FileRelationTrait;

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