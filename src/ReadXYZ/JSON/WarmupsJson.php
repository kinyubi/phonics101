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
        $this->baseConstruct('abc_warmups.json', 'lessonId');
        if (! empty($this->persisted['map'])) return;
        $this->makeMap();
    }

    public function exists($lessonId): bool
    {
        return isset($this->persisted['map'][$lessonId]);
    }

    protected function makeMap(): void
    {
        if ($this->cacheUsed) return;
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        foreach ($this->persisted['objects'] as $object) {
            $items = [];
            foreach ($object->warmups as $item) {
                $items[] = new WarmupItem($item->directions, $item->parts);
            }
            $warmup = new Warmup($object->lessonId, $object->instructions, $items);
            $this->persisted['map'][$object->lessonId] = $warmup;
        }
        $this->stopTimer($timer);
        $this->cacheData();
    }

    /**
     * gets the Warmup instance associated with the specified lessonId
     * @param string $id
     * @return Warmup|null a warmup instance for the given lessonId or null if not found
     */
    public function get(string $id): ?Warmup
    {
        return $this->persisted['map'][$id] ?? null;
    }
}
