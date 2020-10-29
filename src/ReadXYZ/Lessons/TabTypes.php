<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Helpers\Util;
use RuntimeException;

class TabTypes
{
    private static TabTypes $instance;

    /** @var TabType[] */
    private array $tabTypes = [];

    /**
     * TabTypes constructor. Creates an associative array of TabType object with the tabTypeId as key.
     * Subject to change the tab types are stretch(Intro), practice, spinner(Spell), words(Write), fluency,
     * mastery and test. There are left-over Review tab types (all, recent and earlier) that we haven't
     * deleted yet. I've allowed for an 'Instructions' tab that just displays HTML ( probably a twig block name
     * and arguments.
     */
    private function __construct()
    {
        $json = file_get_contents(Util::getReadXyzSourcePath('resources/tabTypes.json'));
        $tabTypes = json_decode($json);
        foreach ($tabTypes as $tabType) {
            $this->tabTypes[$tabType->tabTypeId] = new TabType($tabType);
        }
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
        $tabType = $this->tabTypes[$tabTypeId] ?? null;
        if (null == $tabTypeId) {
            throw new RuntimeException('getTabInfo failed.');
        }
        return $tabType;
    }
}
