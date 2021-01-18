<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\POPO\Warmup;
use App\ReadXYZ\POPO\WarmupItem;

/**
 * Class WarmupsJson houses an array of Warmup objects. Parent methods include get, getAll and getCount
 * @package App\ReadXYZ\JSON
 */
class WarmupsJson
{
    use JsonTrait;
    protected static WarmupsJson   $instance;
    /**
     * WarmupsJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('abc_warmups.json', 'lessonName');
        $this->makeMap();
    }

    public function exists($lessonId): bool
    {
        return isset($this->map[$lessonId]);
    }

    protected function makeMap(): void
    {

        foreach ($this->objects as $object) {
            $items = [];
            foreach ($object->warmups as $item) {
                $items[] = new WarmupItem($item->directions, $item->parts);
            }
            $warmup = new Warmup($object->lessonId, $object->instructions, $items);
            $this->map[$object->lessonId] = $warmup;
        }
    }

    public function get(string $lessonTag): ?Warmup
    {
        $id = LessonsJson::getInstance()->getLessonId($lessonTag);
        return $this->map[$id] ?? null;
    }
}
