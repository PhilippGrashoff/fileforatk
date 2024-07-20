<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

use Atk4\Data\Model;
use PhilippR\Atk4\SecondaryModel\SecondaryModelRelationTrait;

class ModelWithFileRelation extends Model
{

    use SecondaryModelRelationTrait;
    public $table = 'model_with_file_relation';

    protected function init(): void
    {
        parent::init();
        $this->addField('name');

        $this->addSecondaryModelHasMany(File::class);
    }
}