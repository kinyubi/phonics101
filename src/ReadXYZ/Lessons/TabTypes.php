<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\TabTypesData;
use App\ReadXYZ\Enum\TabTypeId;
use App\ReadXYZ\POPO\TabType;
use App\ReadXYZ\Helpers\PhonicsException;

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
     * @throws PhonicsException
     */
    public function getTabInfo(string $tabTypeId): ?TabType
    {
        $realTabType = $this->fixTabName($tabTypeId);
        return $this->tabTypes[$realTabType] ?? null;
    }

    public function isValid(string $tabTypeId): bool
    {
        try {
            $this->fixTabName($tabTypeId);
            return true;
        } catch (PhonicsException $ex) {
            return false;
        }
    }

    /**
     * @param $tabName
     * @return string
     * @throws PhonicsException
     */
    public function fixTabName($tabName)
    {
        $lowerTabType = strtolower($tabName);
        if (not(array_key_exists($lowerTabType, $this->tabTypes))) {
            throw new PhonicsException("$tabName is not a valid tab name or alias.");
        }
        // if it's an alias key, the tabTypeId will be the real
        $realTabName = $this->tabTypes[$tabName]->tabTypeId;
        if (!TabTypeId::isValid($realTabName)) {
            throw new PhonicsException("TabTypeId class and mysql disagree on valid tab type $realTabName.");
        }
        return $realTabName;
    }
}
