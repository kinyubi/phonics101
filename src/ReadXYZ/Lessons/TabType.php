<?php

namespace App\ReadXYZ\Lessons;

use stdClass;

class TabType
{
    public string $tabTypeId;
    public string $tabDisplayAs;
    public string $alias;
    public string $script;
    public string $imageFile;
    public string $html;

    // stdClass fields are from TabTypePOPO:
    public function __construct(stdClass $other)
    {
        $this->tabTypeId = $other->tabTypeId;
        $this->tabDisplayAs = $other->tabDisplayAs;
        $this->alias = $other->alias;
        $this->script = $other->script;
        $this->imageFile = $other->imageFile;
        $this->html = '';
    }

}
