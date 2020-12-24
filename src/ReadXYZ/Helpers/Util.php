<?php

namespace App\ReadXYZ\Helpers;

use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Twig\TwigFactory;
use Throwable;

class Util
{

// ======================== STATIC METHODS =====================

    /**
     * Takes an array of words and converts it to a string list of single-quoted
     * comma-separated words.
     *
     * @param array $arr the array to be converted to a comma-separated string
     *
     * @return string a string of comma-separated words with each word enclosed in single quotes
     */
    public static function arrayToList(array $arr): string
    {
        return "'" . join("','", $arr) . "'";
    }

    /**
     * Determine is a haystack contains a needle (or any needle in an array of needles).
     *
     * @param string $haystack the string to search
     * @param string|array $needles multiple needles may be specified
     *
     * @return bool returns true if $haystack contains $needle
     */
    public static function contains($needles, string $haystack): bool
    {
        if (is_array($needles)) {
            foreach ($needles as $needle) {
                if (false !== strpos($haystack, $needle)) {
                    return true;
                }
            }

            return false;
        } else {
            return false !== strpos($haystack, $needles);
        }
    }

    /**
     * case insensitive version of contains() function.
     *
     * @param string $haystack the string to search for
     * @param string|array $needles the 'needle' or 'needles' to search for
     *
     * @return bool returns true if $haystack contains $needle (case-insensitive)
     */
    public static function contains_ci($needles, string $haystack): bool
    {
        $stack_ci = strtolower($haystack);
        if (is_array($needles)) {
            foreach ($needles as $needle) {
                $needle_ci = strtolower($needle);
                if (false !== strpos($stack_ci, $needle_ci)) {
                    return true;
                }
            }

            return false;
        } else {
            $needle_ci = strtolower($needles);

            return false !== strpos($stack_ci, $needle_ci);
        }
    }

    public static function convertCamelToSnakeCase(string $input): string
    {
        // if everything is already lower case, do nothing
        if (preg_match(Regex::ANY_UPPERCASE, $input) === 0) {
            return $input;
        }
        $pattern = Regex::CAMEL_CASE_TRANSITION;
        // replace lowerCase to uppercase transition with lowercase_lowercase
        return strtolower(preg_replace_callback($pattern, function ($a) {
            return $a[1] . "_" . strtolower($a[2]);
        }, $input));
    }

    public static function convertLessonKeyToLessonName(string $lessonKey): string
    {
        if (Util::startsWith('Blending.', $lessonKey)) {
            return str_replace('Blending.', '', $lessonKey);
        } else {
            return $lessonKey;
        }
    }

    public static function convertLessonNameToLessonKey(string $lessonName): string
    {
        if (Util::startsWith('Blending.', $lessonName)) {
            return $lessonName;
        } else {
            return 'Blending.' . $lessonName;
        }
    }

    /**
     * @see http://gist.github.com/385876
     *
     * @param string $filename The csv file
     * @param string $delimiter the delimiter (default is comma)
     *
     * @return array|bool if successful an array of key value pairs, otherwise false
     */
    public static function csvFileToArray(string $filename = '', string $delimiter = ',')
    {
        if ( ! file_exists($filename) || ! is_readable($filename)) {
            return false;
        }

        $header = null;
        $data   = [];
        if (false !== ($handle = fopen($filename,
                'r'))) {
            while (false !== ($row = fgetcsv($handle,
                    10000,
                    $delimiter))) {
                if ( ! $header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header,
                        $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public static function csvStringToArray(string $list): ?array
    {
        if (($list == null) || empty($list)) {
            return null;
        }
        try {
            return array_map('trim', explode(',', $list));
        } catch (Throwable $throwable) {
            return null;
        }
    }

    public static function dbDate(int $time = 0): string
    {
        if ($time == 0) {
            return date('Y-m-d');
        }
        return date('Y-m-d', $time);
    }

    public static function getHumanReadableDate($date = ''): string
    {
        if ($date) {
            return date('Y-M-j', $date);
        } else {
            return date('Y-M-j');
        }
    }

    public static function getHumanReadableDateTime($date = ''): string
    {
        if ($date) {
            return date('Y-M-j H:i:s', $date);
        } else {
            return date('Y-M-j H:i:s');
        }
    }

    public static function getInput(string $prompt = '?'): string
    {
        printf('%s ', $prompt);

        return trim(fgets(STDIN));
    }

    public static function getProjectPath($filename = ''): string
    {
        return self::stripExtraSlashes(self::reslash($_SERVER['PROJECT_ROOT'] . $filename));
    }

    public static function getPublicPath($filename = ''): string
    {
        return self::stripExtraSlashes($_SERVER['DOCUMENT_ROOT'] . $filename);
    }

    public static function getReadXyzSourcePath($filename = ''): string
    {
        return self::stripExtraSlashes(self::reslash($_SERVER['XYZ_SRC_ROOT'] . $filename));
    }

    public static function isLocal(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ( ! $host) {
            return true;
        }
        return self::contains(['.local', '.test'], $host);
    }

    /**
     * @param $studentCode
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public static function isValidStudentCode($studentCode): bool
    {
        if (Regex::isValidStudentCodePattern($studentCode)) {
            return (new StudentsData())->doesStudentExist($studentCode);
        } else {
            return false;
        }
    }

    /**
     * @param $trainerCode
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public static function isValidTrainerCode($trainerCode): bool
    {
        if (Regex::isValidTrainerCodePattern($trainerCode)) {
            return (new TrainersData())->isValid($trainerCode);
        } else {
            return false;
        }
    }

    /**
     * return first n chars in a string. $len defaults to 1 if not provided.
     *
     * @param string $string The target string to be searched
     * @param int $len The length of the string to be returned
     *
     * @return false|string
     */
    public static function left(string $string, int $len = 1): string
    {
        return substr($string, 0, $len);
    }

    public static function oldUniqueIdToNew(string $oldId): string
    {
        return $oldId . "0Z123456789";
    }

    public static function paddedNumber(string $prefix, int $number, int $padSize = 2): string
    {
        return $prefix . str_pad(strval($number), $padSize, '0', STR_PAD_LEFT);
    }

    /**
     * Takes a string of comma-separated words and surrounds each word with single quotes.
     *
     * @param string csvList a string of comma-separated words
     *
     * @return string
     */
    public static function quoteList(string $csvList): string
    {
        return "'" . str_replace(',', "','", $csvList) . "'";
    }

    public static function redBox(string $message, Throwable $ex = null): string
    {
        $details = $trace = '';
        if ($ex) {
            $details = $ex->getMessage();
            // $trace = $ex->getTraceAsString();
            $trace = Debug::getBackTrace();
        }
        $args = ['errors' => htmlentities("$message\n\n$details\n\n$trace")];

        return TwigFactory::getInstance()->renderTemplate('base', $args);
    }

    /**
     * makes all slashes in a path forward slashes which will work on linux or windows.
     *
     * @param string $path a linux or windows path
     *
     * @return string a path with all forward slashes
     */
    public static function reslash(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * returns true if 'string' starts with 'startString'.
     *
     * @param string $string the target string being searched
     * @param string|array $startString the 'startsWith' string we are looking for
     *
     * @return bool returns true if $string starts with $startString
     */
    public static function startsWith($startString, string $string): bool
    {
        if (is_array($startString)) {
            foreach ($startString as $start) {
                $len = strlen($start);
                if (substr($string, 0, $len) === $start) {
                    return true;
                }
            }

            return false;
        } else {
            $len = strlen($startString);

            return substr($string, 0, $len) === $startString;
        }
    }

    /**
     * case insensitive version of startsWith function.
     *
     * @param string $string the string we want to search
     * @param string|array $startString a substring or array of substrings we want to check against the start of the string
     *
     * @return bool returns true if $string starts with $startString (case-insensitive)
     */
    public static function startsWith_ci($startString, string $string): bool
    {
        $string_ci = strtolower($string);
        if (is_array($startString)) {
            foreach ($startString as $start) {
                $len      = strlen($start);
                $start_ci = strtolower($start);
                if (substr($string_ci, 0, $len) === $start_ci) {
                    return true;
                }
            }

            return false;
        } else {
            $start_ci = strtolower($startString);
            $len      = strlen($start_ci);

            return substr($string_ci, 0, $len) === $start_ci;
        }
    }

    public static function stretchListToArray(string $stretchList): ?array
    {
        if (empty($stretchList)) {
            return null;
        }
        $wordSets = self::csvStringToArray($stretchList);
        $result   = [];
        try {
            foreach ($wordSets as $wordSet) {
                $result[] = array_map('trim', explode('/', $wordSet));
            }
        } catch (Throwable $throwable) {
            return null;
        }

        return $result;
    }

    //     public static function addSoundClass(string $lessonName): string
    //     {
    //         $pattern = '#/([a-zA-Z]+)/#';
    //         return preg_replace($pattern, '<span class="sound">$1</span>', $lessonName);
    //     }
    //
    //     public static function alert(string $message): void
    //     {
    //         echo "<script type='text/javascript'>alert('$message');</script>";
    //     }

    // public static function fixTabName($tabName): string
    // {
    //     switch (strtolower($tabName)) {
    //         case 'stretch':
    //         case 'intro':
    //             return 'intro';
    //         case 'words':
    //         case 'write':
    //             return 'write';
    //         case 'practice':
    //             return 'practice';
    //         case 'spell':
    //         case 'spinner':
    //             return 'spell';
    //         case 'mastery':
    //             return 'mastery';
    //         case 'fluency':
    //             return 'fluency';
    //         case 'test':
    //             return 'test';
    //         case 'warmups':
    //         case 'warmup':
    //         case 'warm-ups':
    //             return 'warmup';
    //         default:
    //             throw new PhonicsException("$tabName is not a recognized tab name.");
    //     }
    // }

    /**
     * strips extra forward slashes from a uri or path.
     *
     * @param string $uri uri or path
     *
     * @return string a valid path or uri
     */
    public static function stripExtraSlashes(string $uri): string
    {
        if (self::startsWith('http', $uri)) {
            $start = substr($uri, 0, 8);
            $end   = substr($uri, 8);
            $end2  = str_replace('//', '/', $end);

            return $start . $end2;
        } else {
            $uri = self::reslash($uri);

            return str_replace('//', '/', $uri);
        }
    }

    /**
     * Removes the namespace from a class name.
     *
     * @param string $fullClassName strips the namespace from a fully-qualified class name
     * @return string
     */
    public static function stripNameSpace(string $fullClassName): string
    {
        $pos = strrpos($fullClassName, '\\');
        if (false === $pos) {
            return $fullClassName;
        }

        return substr($fullClassName, $pos + 1);
    }

    public static function testingInProgress(): bool
    {
        return defined('TESTING_IN_PROGRESS');
    }

}
