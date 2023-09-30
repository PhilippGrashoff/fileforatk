<?php

declare(strict_types=1);

namespace PhilippR\Atk4\File\Tests;

use PhilippR\Atk4\File\SafeFileName;
use traitsforatkdata\TestCase;

class SafeFileNameTest extends TestCase
{

    public function testReplaceSpecialChars(): void
    {
        $res = SafeFileName::replaceSpecialChars('äöüÄÖÜß-:');
        self::assertSame(
            'aeoeueAeOeUess__',
            $res
        );
    }

    public function testRemoveDisallowedChars(): void
    {
        $res = SafeFileName::removeDisallowedChars(';,! alla.jpg,?=)(');
        self::assertSame(
            'alla.jpg',
            $res
        );
    }

    public function testcreateSafeFileName(): void
    {
        $res = SafeFileName::replaceSpecialChars('Änderung-02.jpg');
        self::assertSame(
            'Aenderung_02.jpg',
            $res
        );
    }
}
