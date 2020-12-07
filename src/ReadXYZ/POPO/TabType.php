<?php

namespace App\ReadXYZ\POPO;

use stdClass;

class TabType
{
    public string $tabTypeId;
    public string $tabDisplayAs;
    public string $alias;
    public string $script;
    public string $imageFile;

    // stdClass fields are from TabTypePOPO:
    public function __construct(stdClass $other)
    {
        $this->tabTypeId = $other->tabTypeId;
        $this->tabDisplayAs = $other->tabDisplayAs;
        $this->alias = $other->alias;
        $this->script = $other->script;
        $this->imageFile = $other->imageFile ?? $other->iconUrl;
    }

}
