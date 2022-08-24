<?php

declare(strict_types=1);

namespace fileforatk\tests\testclasses;

use fileforatk\File;

class FileMock extends File
{

    public function uploadFile($f): bool
    {
        $this->set('value', $f['name']);
        $this->set('path', $f['path']);
        return true;
    }
}
