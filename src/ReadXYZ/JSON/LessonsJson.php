<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Lessons\Lesson;
use stdClass;

/**
 * Class LessonsJson is a singleton class. Converts JSON to Lesson objects.
 * parent methods include getAll (returns $t
 * @package App\ReadXYZ\JSON
 */
class LessonsJson extends AbstractJson
{
    protected array $aliasMap = [];
    protected array $accordion = [];
    protected array $titles    = [];

    protected function __construct()
    {
        parent::__construct('abc_keychain.json', 'lessonId');
        $this->createAccordionTemplate();
    }

    public static function getInstance(): LessonsJson
    {
        return parent::getInstanceBase(__CLASS__);

    }

    /**
     * Try everything to get a lessonId
     * @param string $lessonTag
     * @return string
     */
    public function getLessonId(string $lessonTag): string
    {
        return $this->aliasMap[$lessonTag] ?? '';
    }

    /**
     * Try everything to get a lessonId
     * @param string $lessonTag
     * @return string
     */
    public function getLessonCode(string $lessonTag): string
    {
        return $this->getLessonId($lessonTag);
    }

    public function getLessonName(string $lessonTag): string
    {
        $id = $this->getLessonId($lessonTag);
        if (! $id) return '';
        return $this->map[$id]->lessonName ?? '';
    }

    public function getGroupName(string $lessonTag): string
    {
        $id = $this->getLessonId($lessonTag);
        if (! $id) return '';
        return GroupsJson::getInstance()->getGroupName($this->map[$id]->groupCode);
    }

    public function getGroupCode(string $lessonTag): string
    {
        $id = $this->getLessonId($lessonTag);
        if (! $id) return '';
        return GroupsJson::getInstance()->getGroupCode($this->map[$id]->groupCode);
    }

    public function getAccordion(): array
    {
        return $this->accordion;
    }

    public function getLesson(string $lessonTag): ?stdClass
    {
        return $this->get($lessonTag);
    }

    /**
     * Overrides parent::makeMap. Need to create the field lessonCode in case its referenced
     * @param stdClass[] $objects
     * @return void
     */
    protected function makeMap(array $objects): void
    {
        $key = $this->primaryKey;
        $ordinal = 0;
        foreach ($objects as $object) {
            $lessonId = $object->lessonId;
            $title = $object->lessonName;
            $object->lessonCode = $lessonId;
            $object->ordinal = ++$ordinal;
            $this->map[$object->$key] = new Lesson($object);
            $this->aliasMap[$lessonId] = $lessonId;
            if (isset($object->lessonName)) {
                $this->aliasMap[$object->lessonName] = $lessonId;
                $this->titles[$object->lessonName]   = $title;
            }

            if (isset($object->alternateNames)) {
                foreach ($object->alternateNames as $name) {
                    $this->aliasMap[$name] = $lessonId;
                    $this->titles[$name]   = $title;
                }
            }
        }

    }

    /**
     * create an array of display group and lesson names
     * [
     *      {
     *          groupName => [
     *              [lessonName => 0]
     *          ]
     *      }
     * ]
     */
    protected function createAccordionTemplate(): void
    {
        $groupCodes = GroupsJson::getInstance()->getGroupCodes();
        foreach ($groupCodes as $code) {
            $this->accordion[$code] = [];
        }
        foreach ($this->map as $item) {
            $id = $item->lessonId;
            $this->accordion[$this->getGroupName($id)][$id] = 0;
        }
    }

    /**
     * @throws PhonicsException
     */
    public function getAccordionWithMastery(): array
    {
        $accordion = $this->accordion;
        $mastery = (new StudentLessonsData())->getLessonMastery();
        foreach($mastery as $lesson) {
            if (isset($accordion[$lesson->groupCode][$lesson->lessonCode])) {
                $accordion[$lesson->groupCode][$lesson->lessonCode] = MasteryLevel::toIntegral($lesson->masteryLevel);
            }
        }
        return $accordion;
    }
}
