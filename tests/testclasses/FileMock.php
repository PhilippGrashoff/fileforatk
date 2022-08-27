<?php

declare(strict_types=1);

namespace fileforatk\tests\testclasses;

use fileforatk\File;

class FileMock extends File
{

    public function uploadFile($f): void
    {
        $this->set('value', $f['name']);
        if(isset($f['path'])) {
            $this->set('path', $f['path']);
        }
    }
}
