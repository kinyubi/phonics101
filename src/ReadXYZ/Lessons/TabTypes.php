<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\TabTypesData;
use App\ReadXYZ\POPO\TabType;
use RuntimeException;

class TabTypes
{
    private static TabTypes $instance;

    /** @var TabType[] */
    private array $tabTypes;

    /**
     * TabTypes constructor. Creates an associative array of TabType object with the tabTypeId as key.
     * Subject to change the tab types are warmup, stretch(Intro), practice, spinner(Spell), words(Write),
     * fluency, mastery and test. There are left-over Review tab types (all, recent and earlier) that we haven't
     * deleted yet. I've allowed for an 'Instructions' tab that just displays HTML ( probably a twig block name
     * and arguments.
     */
    private function __construct()
    {
        $data = new TabTypesData();
        $this->tabTypes = $data->getAll();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new TabTypes();
        }

        return self::$instance;
    }


    /**
     * @param string $tabTypeId
     *
     * @return TabType|null
     */
    public function getTabInfo(string $tabTypeId): ?TabType
    {
        $lowerTabType = strtolower($tabTypeId);
        return $this->tabTypes[$lowerTabType] ?? null;
    }

    public function isValid(string $tabTypeId) {
        $lowerTabType = strtolower($tabTypeId);
        return array_key_exists($lowerTabType, $this->tabTypes);
    }

    public function fixTabName($tabName)
    {
        if (not($this->isValid($tabName))) {
            throw new RuntimeException("$tabName is not a valid tab name or alias.");
        }
        // if it's an alias key, the tabTypeId will be the real
        return $this->tabTypes[$tabName]->tabTypeId;
    }
}
