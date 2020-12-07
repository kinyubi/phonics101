<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class Regex extends Enum
{
    const ANY_UPPERCASE = '/[A-Z]/';
    const CAMEL_CASE_TRANSITION = '/([a-z])([A-Z])/'; // lowercase letter followed by uppercase letter
    const VALID_STUDENT_CODE = '/S[0-9a-f]{14}Z[0-9]{8}/'; // S, 14 hex digits, Z, 9 numeric digits
    const VALID_TRAINER_CODE = '/U[0-9a-f]{14}Z[0-9]{8}/'; // T, 14 hex digits, Z, 9 numeric digits
    const PARENTHETICAL_NUMBER = '/\((\d+)\)/';
    const LESSON_CODE_PATTERN = '^/G\d\dL\d\d$/';
    const GROUP_CODE_PATTERN = '^/G\d\d$/';

    public static function extractSqlFieldLength(string $fieldDefinition): int
    {
        $match =Regex::getFirstMatch(Regex::PARENTHETICAL_NUMBER, $fieldDefinition);
        return $match ? intval($match) : 0;
    }

    public static function isValidStudentCodePattern(string $studentCode): bool
    {
        return Regex::isMatch(Regex::VALID_STUDENT_CODE, $studentCode);
    }

    public static function isValidTrainerCodePattern(string $trainerCode): bool
    {
        return Regex::isMatch(Regex::VALID_TRAINER_CODE, $trainerCode);
    }

    public static function isValidLessonCodePattern(string $lessonCode): bool
    {
        return Regex::isMatch(Regex::LESSON_CODE_PATTERN, $lessonCode);
    }

    public static function isValidGroupCodePattern(string $groupCode): bool
    {
        return Regex::isMatch(Regex::GROUP_CODE_PATTERN, $groupCode);
    }

    private static function isMatch(string $pattern, string $value): bool
    {
        return (1 === preg_match($pattern, $value));
    }

    /**
     * gets the first substring to match the pattern
     * @param string $pattern the pattern to match
     * @param string $subject the string to be searched
     * @return string
     */
    private static function getFirstMatch(string $pattern, string $subject): string
    {
        preg_match($pattern, $subject, $matches);
        return empty($matches[1]) ? '' : $matches[1];
    }
}
