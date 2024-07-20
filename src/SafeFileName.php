<?php declare(strict_types=1);

namespace PhilippR\Atk4\File;

use Atk4\Data\Exception;

/**
 * The Purpose of this class is to remove any chars except 0-9, a-z, A-Z, _ and . from a filename.
 * Also converts german special chars and replaces : and - with _
 */
class SafeFileName
{

    /**
     * @param string $input
     * @return string
     * @throws Exception
     */
    public static function createSafeFileName(string $input): string
    {
        return self::removeDisallowedChars(self::replaceSpecialChars($input));
    }

    /**
     * @param string $input
     * @return string
     */
    public static function replaceSpecialChars(string $input): string
    {
        $search = array("ä", "ö", "ü", "ß", "Ä", "Ö", "Ü", ':', '-');
        $replace = array("ae", "oe", "ue", "ss", "Ae", "Oe", "Ue", '_', '_');
        return str_replace($search, $replace, $input);
    }

    /**
     * @param string $input
     * @return string
     * @throws Exception
     */
    public static function removeDisallowedChars(string $input): string
    {
        $result =  preg_replace('/[^a-zA-Z0-9_.]/', '', $input);
        if($result === null) {
            throw new Exception('Error in preg_replace() in ' . __FUNCTION__); //@codeCoverageIgnore
        }

        return $result;
    }
}
