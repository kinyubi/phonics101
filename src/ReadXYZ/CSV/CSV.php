<?php


namespace App\ReadXYZ\CSV;


use App\ReadXYZ\Helpers\Util;
use stdClass;
use Throwable;

class CSV
{
    const CSV_DATA_DIR = __DIR__ . '/data';

    protected static object   $instance;
    protected string          $sourceFile = '';
    protected array           $map = [];
    protected array           $array;
    protected string          $keyField;

    protected function __construct(string $sourceFile='', string $keyField = '')
    {
        if (not(empty($sourceFile))) {
            $this->sourceFile = self::CSV_DATA_DIR . '/' . $sourceFile;
            $this->keyField = $keyField;

            $array = self::fileToArray($this->sourceFile);
            if (not(empty($keyField))) {
                foreach ($array as $index => $item) {
                    $key = $item[$keyField];
                    $this->map[$key] = $index;
                    $this->array[] = $item;
                }
            }
        }

    }

    protected function getSourceFileName(): string
    {
        return $this->sourceFile;
    }

    public function getRecord(string $userName): array
    {
        return $this->array[$this->map[$userName]] ?? [];
    }

    /** fields are Username, First Name, Last Name, Display Name, Email, Website, Role
     * @param string $userName
     * @param string $fieldName
     * @return string
     */
    public function getValue(string $userName, string $fieldName): string
    {
        return $this->getRecord($userName)[$fieldName] ?? '';
    }

    /**
     * returns an array of a users studentx_first_name fields.
     * @param string $userName
     * @return array
     */
    public function getStudentNames(string $userName): array
    {
        $record = $this->getRecord($userName);
        if (empty($record)) return [];
        return [$record['student1_first_name'], $record['student2_first_name'], $record['student3_first_name']];
    }

    /**
     * returns an s2Members student_ids field as as array
     * @param string $userName
     * @return array
     */
    public function getStudentIds(string $userName): array
    {
        return self::listToArray($this->getValue($userName, 'student_ids'));
    }

// ======================== STATIC METHODS =====================
    /**
     * @see http://gist.github.com/385876
     *
     * @param string $filename The csv file
     * @param string $delimiter the delimiter (default is comma)
     *
     * @return array|bool if successful an array of key value pairs, otherwise false
     */
    public static function fileToArray(string $filename = '', string $delimiter = ',')
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
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * convert a comma-separated list to an array
     * @param string $list
     * @return array
     */
    public static function listToArray(string $list): ?array
    {
        if (($list == null) || empty($list)) {
            return [];
        }
        try {
            return array_map('trim', explode(',', $list));
        } catch (Throwable $throwable) {
            return [];
        }
    }

    public function smartQuotes($value): string
    {
        if (empty($value)) return "''";
        return "'" . addslashes($value) . "'";
    //    return "'" . $this->connection->real_escape_string($value) . "'";
    }

    /**
     * Takes a string of comma-separated words and surrounds each word with single quotes.
     *
     * @param string csvList a string of comma-separated words
     * @return string each word is encapsulated in single quotes.
     */
    public function quoteList(string $csvList): string
    {
        $array = self::listToArray($csvList);
        $newArray = array_map([__CLASS__, 'smartQuotes'], $array);
        return implode(',', $newArray);

    }

    /**
     * converts a list like "a/b/c,d/e/f" to [ ['a','b','c'], ['d','e','f'] ]
     * @param string $stretchList
     * @return array|null
     */
    public static function stretchListToArray(string $stretchList): ?array
    {
        if (empty($stretchList)) {
            return null;
        }
        $wordSets = self::listToArray($stretchList);
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

// ======================== PROTECTED METHODS =====================
    public static function getInstance()
    {
        return self::getInstanceBase(__CLASS__);
    }

    protected static function getInstanceBase(string $class)
    {
        $fullClass =  Util::startsWith('App', $class) ? $class : 'App\\ReadXYZ\\JSON\\' . $class;
        if ( ! isset(self::$instance)) {
            self::$instance = new $fullClass();
        }

        return self::$instance;
    }

}
