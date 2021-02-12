<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Lessons\Lesson;
use App\ReadXYZ\Models\Session;
use JsonSerializable;

/**
 * Class LessonsJson is a singleton class. Converts JSON to Lesson objects.
 * parent methods include getAll (returns $t
 * @package App\ReadXYZ\JSON
 */
class LessonsJson implements JsonSerializable
{
    use JsonTrait;
    protected static LessonsJson   $instance;

    /**
     * LessonsJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->cachingEnabled = false;
        $this->baseConstruct('abc_lessons.json', 'lessonId');
        $this->makeMap();
    }

    public function get(string $tag): ?Lesson
    {
        $key = $this->persisted['aliasMap'][$tag] ?? '';
        if (empty($key)) return null;
        return  $this->persisted['map'][$key];
    }

    /**
     * Try everything to get a lessonId
     * @param string $lessonTag
     * @return string|false
     */
    public function getLessonId(string $lessonTag)
    {
        return $this->persisted['aliasMap'][$lessonTag] ?? false;
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
        return $this->persisted['map'][$id]->lessonName ?? false;
    }

    /**
     * @param string $lessonTag
     * @return string|false
     */
    public function getGroupName(string $lessonTag)
    {
        $id = $this->getLessonId($lessonTag);
        if ($id === false) return false;
        return GroupsJson::getInstance()->getGroupName($this->persisted['groupCodes'][$id]) ?? false;
    }

    /**
     * @param string $lessonTag
     * @return string|false
     */
    public function getGroupCode(string $lessonTag)
    {
        $id = $this->getLessonId($lessonTag);
        if ($id === false) return false;
        return $this->persisted['groupCodes'][$id] ?? false;
    }

    public function getAccordion(): array
    {
        return $this->persisted['accordion'];
    }

    public function getLesson(string $lessonTag): ?Lesson
    {
        return $this->get($lessonTag);
    }

    public function getRandomLessonCode(): Lesson
    {
        $index = random_int(0, count($this->persisted['objects']) - 1);
        return $this->persisted['objects'][$index]->lessonCode;
    }

    public function getOrdinal(string $lessonTag): int
    {
        $id = $this->getLessonId($lessonTag);
        if ($id === false) return 0;
        return $this->persisted['map'][$id]->ordinal ?? 0;
    }

    /**
     * Overrides parent::makeMap. Need to create the field lessonCode in case its referenced
     * @return void
     */
    protected function makeMap(): void
    {
        if ($this->cacheUsed) return;
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        $ordinal = 0;
        $this->persisted['aliasMap'] = [];
        foreach ($this->persisted['objects'] as $objectX) {
            $object = clone $objectX;
            $lessonId = $object->lessonId;
            $groupCode = $object->groupCode;
            $lessonName = $object->lessonName;
            $alternateNames = $object->alternateNames ?? [];
            $lesson = new Lesson($object);
            $lesson->ordinal = ++$ordinal;
            $this->persisted['map'][$lessonId] = $lesson;
            $this->persisted['aliasMap'][$lessonId] = $lessonId;
            $this->persisted['groupCodes'][$lessonId] = $groupCode;
            $this->persisted['aliasMap'][$lessonName] = $lessonId;
            // $this->printWhack();
            foreach ($alternateNames as $name) {
                if (empty($name)) continue;
                $this->persisted['aliasMap'][$name] = $lessonId;
            }

        }
        $this->createAccordionTemplate();
        $this->stopTimer($timer);
        $this->cacheData();
    }

    // private function printWhack()
    // {
    //     $urls = [];
    //     foreach($this->persisted['map'] as $key => $lesson) {
    //         foreach($lesson->games as $tab => $games) {
    //             foreach($games as $game) {
    //                 if ($game->gameTypeId == "whack-a-mole") {
    //                     $urls[] = $game->url;
    //                 }
    //             }
    //         }
    //
    //     }
    //     $x = 45;
    // }

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
            $this->persisted['accordion'][$code] = ['groupName' => $name, 'lessons' => []];
        }
        foreach ($this->persisted['map'] as $item) {
            $id = $item->lessonId;
            $groupCode = $item->groupCode;
            $this->persisted['accordion'][$groupCode]['lessons'][$id] = ['lessonName' => $item->lessonName, 'mastery' => 0];
        }
    }

    /**
     * @throws PhonicsException
     */
    public function getAccordionWithMastery(string $studentTag=''): array
    {
        $accordion = $this->persisted['accordion'];
        $studentCode = empty($studentTag) ? Session::getStudentCode() : (new StudentsData())->getStudentCode($studentTag);

        if (empty($studentCode)) return $accordion;
        $mastery = (new StudentLessonsData($studentCode))->getLessonMastery();
        foreach($mastery as $lesson) {
            $groupCode = $this->persisted['groupCodes'][$lesson->lessonCode] ?? false;
            if ($groupCode) {
                $id = $lesson->lessonCode;
                if (isset($this->persisted['accordion'][$groupCode]['lessons'][$id])) {
                    $accordion[$groupCode]['lessons'][$id]['mastery'] = MasteryLevel::toIntegral($lesson->masteryLevel);
                }
            }

        }
        return $accordion;
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
