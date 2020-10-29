<?php


namespace App\ReadXYZ\POPO;


use App\ReadXYZ\Helpers\Util;

class WarmupItem
{

    public string $directions;
    /**
     * @var string[]
     */
    public array $parts = [];

    public function __construct(string $directions, array $parts)
    {
        $this->directions = Util::addSoundClass($directions);
        foreach ($parts as $part) {
            $this->parts[] = Util::addSoundClass($part);
        }
    }
}
