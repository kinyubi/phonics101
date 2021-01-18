<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class Regex
 * @package App\ReadXYZ\Enum
 * @see https://www.phpliveregex.com/
 */
class Regex extends Enum
{
    const ANY_UPPERCASE = '/[A-Z]/';
    const ALL_ALPHA = '/^[a-zA-Z]+$/';
    const CAMEL_CASE_TRANSITION = '/([a-z])([A-Z])/'; // lowercase letter followed by uppercase letter
    const VALID_STUDENT_CODE = '/^S[0-9a-f]{14}Z[0-9]{8}$/'; // S, 14 hex digits, Z, 9 numeric digits
    const VALID_OLD_STUDENT_CODE = '/^S[0-9a-f]{13}$/'; // S, 13 hex digits
    const VALID_OLD_TRAINER_CODE = '/^U[0-9a-f]{13}$/'; // U, 13 hex digits
    const VALID_CONVERTED_STUDENT_CODE = '/^S[0-9a-f]{13}0Z123456789$/';
    const VALID_TRAINER_CODE = '/^U[0-9a-f]{14}Z[0-9]{8}$/'; // T, 14 hex digits, Z, 9 numeric digits
    const PARENTHETICAL_NUMBER = '/\((\d+)\)/';
    const LESSON_CODE_PATTERN = '/^G\d\dL\d\d$/';
    const GROUP_CODE_PATTERN = '/^G\d\d$/';
    const KEYCHAIN_CODE_PATTERN = '/^[Gk]\d\d$/';
    const NAME_PATTERN = '/^[A-Za-z][a-z]+$/';
    const VALID_DATE_PATTERN = '/^2[01][0-9]{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/';
    const VERSION_PATTERN = '/^[0-9]{1,2}\.[0-9]{4}\.[0-9]$/';

    public static function extractSqlFieldLength(string $fieldDefinition): int
    {
        $match = Regex::getFirstMatch(Regex::PARENTHETICAL_NUMBER, $fieldDefinition);
        return $match ? intval($match) : 0;
    }

    public static function isValidStudentCodePattern(string $studentCode): bool
    {
        $isMatch = Regex::isMatch(Regex::VALID_STUDENT_CODE, $studentCode);
        if (!$isMatch) $isMatch = Regex::isMatch(Regex::VALID_CONVERTED_STUDENT_CODE, $studentCode);
        return $isMatch;
    }

    public static function isValidEmail(string $trainer): bool
    {
        return filter_var($trainer, FILTER_VALIDATE_EMAIL);
    }

    /**
     * The trainer1 field of an old student record is input. It determines is trainer1 is a composite email,
     * meaning it is an email address, followed by a hyphen, followed by the student's first name. An object is
     * returned with the fields 'success', 'email' and 'student'.
     * 'success' is true if the value validated as an email address or a composite email address.
     * 'email' contains just the email portion of the trainer.
     * 'student' contains the student first name if trainer was a composite email, otherwise it is an empty string.
     * @param string $trainer
     * @return object
     */
    public static function parseCompositeEmail(string $trainer)
    {

        $pos = false;
        $pos1 = strrpos($trainer, '-');
        $pos2 = strrpos($trainer, '.');
        if (($pos1 !== false) && ($pos2 !== false)) {
            $pos = ($pos1 > $pos2) ? $pos1 : false;
        }
        if ($pos === false)
        {
            // we don't have a hyphen after the final period
            if (!filter_var($trainer, FILTER_VALIDATE_EMAIL)) {
                return (object)['success' => false, 'email' => $trainer, 'student' => ''];
            } else {
                return (object)['success' => true, 'email' => $trainer, 'student' => ''];
            }
        }
        $email = substr($trainer, 0, $pos);
        $student = substr($trainer, $pos + 1);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return (object) ['success' => true, 'email' => $email, 'student' => $student];
        } else {
            return (object) ['success' => false,  'email' => $email, 'student' => $student];
        }
    }

    public static function isValidOldStudentCodePattern(string $studentCode): bool
    {
        return Regex::isMatch(Regex::VALID_OLD_STUDENT_CODE, $studentCode);
    }

    public static function isValidOldTrainerCodePattern(string $studentCode): bool
    {
        return Regex::isMatch(Regex::VALID_OLD_TRAINER_CODE, $studentCode);
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

    public static function isMatch(string $pattern, string $value): bool
    {
        return (1 === preg_match($pattern, $value));
    }

    public static function isValidVersion(string $value): bool
    {
        return Regex::isMatch(Regex::VERSION_PATTERN, $value);
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
