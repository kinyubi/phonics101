<?php


namespace App\ReadXYZ\JSON;

use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class PhrasesJson. map is an associative array of [id => phrase]
 * @package App\ReadXYZ\JSON
 */
class PhrasesJson
{
    use JsonTrait;

    protected static PhrasesJson   $instance;

    /**
     * PhrasesJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('abc_phrases.json', 'id');
        $this->makeMap();
    }

    /**
     * Recognize two field names 'phrase' and 'phrases'. if 'phrase' is a string, it is used as is.
     * If 'phrase' is an array of strings, the strings are concatenated joined by a space.
     * If 'phrases' is a string, a line feed is appended to the string, otherwise the strings
     * are concatenated joined with line feeds. HTML can be used. Using phrases with HTML may result
     * in more readable HTML.
     */
    protected function makeMap()
    {
        if ($this->cacheUsed) return;
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        foreach ($this->persisted['objects'] as $object) {
            if (isset($object->phrases)) {
                if (is_array($object->phrases)) {
                    $this->persisted['map'][$object->id] = implode("\n", $object->phrases) . "\n";
                } else {
                    $this->persisted['map'][$object->id] = $object->phrases . "\n";
                }
            } elseif (isset($object->phrase)) {
                if (is_array($object->phrase)) {
                    $this->persisted['map'][$object->id] = implode(" ", $object->phrase);
                } else {
                    $this->persisted['map'][$object->id] = $object->phrase;
                }
            }
        }
        $this->stopTimer($timer);
        $this->cacheData();
    }

    public function get(string $key)
    {
        return $this->persisted['map'][$key] ?? '';
    }

    public function exists(string $keyValue): bool
    {
        return not(empty($this->get($keyValue)));
    }

}
