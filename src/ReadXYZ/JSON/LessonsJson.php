<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Lessons\Lesson;
use App\ReadXYZ\Models\Session;
use stdClass;

/**
 * Class LessonsJson is a singleton class. Converts JSON to Lesson objects.
 * parent methods include getAll (returns $t
 * @package App\ReadXYZ\JSON
 */
class LessonsJson
{
    use JsonTrait;
    protected static LessonsJson   $instance;

    /**
     * @var array  [ [lessonId/lessonName/alternateName => lessonId] ]
     */
    protected array $aliasMap = [];

    /**
     * @var array // [ [groupCode => ['groupName' => groupName, 'lessons' => [ ['lessonId' => lessonId, 'lessonName' => lessonName] ]
     */
    protected array $accordion = [];

    /**
     * @var array [ [lessonId/groupCode => lessonName/groupName] ]
     */
    protected array $titles    = [];

    /**
     * @var array [ [lessonId => groupCode ]
     */
    protected array $groupCodes = [];

    /**
     * LessonsJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('abc_lessons.json', 'lessonId');
        $this->makeMap();
        $this->createAccordionTemplate();
    }

    public function get(string $tag): ?Lesson
    {
        $key = $this->aliasMap[$tag] ?? '';
        if (empty($key)) return null;
        return  $this->map[$key];
    }

    /**
     * Try everything to get a lessonId
     * @param string $lessonTag
     * @return string|false
     */
    public function getLessonId(string $lessonTag)
    {
        return $this->aliasMap[$lessonTag] ?? false;
    }

    /**
     * Try everything to get a lessonId
     * @param string $lessonTag
     * @return string|false
     */
    public function getLessonCode(string $lessonTag)
    {
        return $this->getLessonId($lessonTag);
    }

    /**
     * @param string $lessonTag
     * @return string|false
     */
    public function getLessonName(string $lessonTag)
    {
        $id = $this->getLessonId($lessonTag);
        if ($id === false) return false;
        return $this->map[$id]->lessonName ?? false;
    }

    /**
     * @param string $lessonTag
     * @return string|false
     */
    public function getGroupName(string $lessonTag)
    {
        $id = $this->getLessonId($lessonTag);
        if ($id === false) return false;
        return GroupsJson::getInstance()->getGroupName($this->groupCodes[$id]) ?? false;
    }

    /**
     * @param string $lessonTag
     * @return string|false
     */
    public function getGroupCode(string $lessonTag)
    {
        $id = $this->getLessonId($lessonTag);
        if ($id === false) return false;
        return $this->groupCodes[$id] ?? false;
    }

    public function getAccordion(): array
    {
        return $this->accordion;
    }

    public function getLesson(string $lessonTag): ?Lesson
    {
        return $this->get($lessonTag);
    }

    /**
     * Overrides parent::makeMap. Need to create the field lessonCode in case its referenced
     * @return void
     */
    protected function makeMap(): void
    {
        $key = $this->primaryKey;
        $ordinal = 0;
        foreach ($this->objects as $object) {
            $lessonId = $object->lessonId;
            $title = $object->lessonName;
            $object->lessonCode = $lessonId;
            $object->ordinal = ++$ordinal;
            $this->map[$object->$key] = new Lesson($object);
            $this->aliasMap[$lessonId] = $lessonId;
            $this->groupCodes[$lessonId] = $object->groupCode;
            if (isset($object->lessonName)) {
                $this->aliasMap[$object->lessonName] = $lessonId;
                $this->titles[$lessonId]   = $title;
            }

            if (isset($object->alternateNames)) {
                foreach ($object->alternateNames as $name) {
                    if (empty($name)) continue;
                    $this->aliasMap[$name] = $lessonId;
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
        $groupMap = GroupsJson::getInstance()->getGroupCodeToNameMap();
        foreach ($groupMap as $code =>$name) {
            $this->accordion[$code] = ['groupName' => $name, 'lessons' => []];
        }
        foreach ($this->map as $item) {
            $id = $item->lessonId;
            $groupCode = $item->groupCode;
            $this->accordion[$groupCode]['lessons'][$id] = ['lessonName' => $item->lessonName, 'mastery' => 0];
        }
    }

    /**
     * @throws PhonicsException
     */
    public function getAccordionWithMastery(string $studentTag=''): array
    {
        $accordion = $this->accordion;
        $studentCode = empty($studentTag) ? Session::getStudentCode() : (new StudentsData())->getStudentCode($studentTag);

        if (empty($studentCode)) return $accordion;
        $mastery = (new StudentLessonsData($studentCode))->getLessonMastery();
        foreach($mastery as $lesson) {
            $groupCode = $this->groupCodes[$lesson->lessonCode] ?? false;
            if ($groupCode) {
                $id = $lesson->lessonCode;
                if (isset($accordion[$groupCode]['lessons'][$id])) {
                    $accordion[$groupCode]['lessons'][$id]['mastery'] = MasteryLevel::toIntegral($lesson->masteryLevel);
                }
            }

        }
        return $accordion;
    }
}
