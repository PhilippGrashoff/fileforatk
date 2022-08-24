<?php declare(strict_types=1);

namespace fileforatk;

/**
 * The Purpose of this class is to remove any chars except 0-9, a-z, A-Z, _ and . from a filename.
 * Also converts german special chars and replaces : and - with _
 */
class SafeFileName
{

    public static function createSafeFileName(string $input): string
    {
        return self::removeDisallowedChars(self::replaceSpecialChars($input));
    }

    public static function replaceSpecialChars(string $input): string
    {
        $search = array("ä", "ö", "ü", "ß", "Ä", "Ö", "Ü", ':', '-');
        $replace = array("ae", "oe", "ue", "ss", "Ae", "Oe", "Ue", '_', '_');
        return str_replace($search, $replace, $input);
    }

    public static function removeDisallowedChars(string $input): string
    {
        return preg_replace('/[^a-zA-Z0-9_.]/', '', $input);
    }
}
