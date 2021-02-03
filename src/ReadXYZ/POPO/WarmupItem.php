<?php


namespace App\ReadXYZ\POPO;



use JsonSerializable;

class WarmupItem implements JsonSerializable
{

    public string $directions;
    /**
     * @var string[]
     */
    public array $parts = [];

    public function __construct(string $directions='', array $parts=[])
    {
        $this->directions = $directions;
        foreach ($parts as $part) {
            $this->parts[] = $part;
        }
    }

    public function jsonSerialize()
    {
        return [
            'directions' => $this->directions,
            'parts' => $this->parts,
        ];
    }

    public static function __set_state($array)
    {
        $warmupItem = new WarmupItem();
        $warmupItem->directions = $array['directions'];
        $warmupItem->parts = $array['parts'];
        return $warmupItem;
    }
}
