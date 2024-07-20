<?php declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests\Testclasses;

use PhilippR\Atk4\File\File;

class UploadedFileMock extends File
{

    public function uploadFile($f): void
    {
        $this->set('value', $f['name']);
        if(isset($f['path'])) {
            $this->set('path', $f['path']);
        }
    }
}
