<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\POPO\Warmup;
use App\ReadXYZ\POPO\WarmupItem;

/**
 * Class WarmupsJson houses an array of Warmup objects. Parent methods include get, getAll and getCount
 * @package App\ReadXYZ\JSON
 */
class WarmupsJson extends AbstractJson
{
    protected function __construct()
    {
        parent::__construct('abc_warmups.json', 'lessonId');
    }

    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }

    public function exists($lessonTag): bool
    {
        $lessonId = LessonsJson::getInstance()->getLessonId($lessonTag);
        return isset($this->map[$lessonId]);
    }

    protected function makeMap(array $objects): void
        {

            foreach ($objects as $object) {
                // IMPORTANT: Right now what's in lessonId in the JSON is really lessonName
                $lessonId = LessonsJson::getInstance()->getLessonId($object->lessonId);
                $items = [];
                foreach ($object->warmups as $item) {
                    $items[] = new WarmupItem($item->directions, $item->parts);
                }
                $warmup = new Warmup($lessonId, $object->instructions, $items);
                $this->map[$lessonId] = $warmup;
            }
        }
}
