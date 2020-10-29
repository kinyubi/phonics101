<?php

namespace App\ReadXYZ\Lessons;

use Exception;
use App\ReadXYZ\Helpers\Util;

class CsvList
{
    private array $csvArray;
    private array $alternateToRealLessonNameMap = [];
    private array $alternateNames = [];
    private static CsvList $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CsvList();
        }

        return self::$instance;
    }

    /**
     * CsvList constructor.
     *
     * @throws Exception when unable to find csv file at any of the expected locations
     */
    private function __construct()
    {
        $fileName = Util::getReadXyzSourcePath('resources/old/compare.csv');

        $this->csvArray = Util::csvFileToArray($fileName);
        foreach ($this->csvArray as $lesson) {
            $realName = trim($lesson['NewLessonName']);
            $originalName = trim($lesson['OriginalLessonName']);
            if (empty($realName)) {
                continue;
            }
            $this->alternateToRealLessonNameMap[$realName] = $realName;
            $this->alternateToRealLessonNameMap[$originalName] = $realName;
            $this->alternateNames[$realName] = [$realName, $originalName];
        }
    }

    public function getArray(): array
    {
        return $this->csvArray;
    }

    public function getRealLessonName($lessonName): string
    {
        $trimmedName = trim($lessonName);

        return $this->alternateToRealLessonNameMap[$trimmedName] ?? $trimmedName;
    }

    public function getAlternateNames($lessonName) : array
    {
        $realName = $this->getRealLessonName(trim($lessonName));
        return $this->alternateNames[$realName] ?? [];

    }
}
