<?php


namespace App\ReadXYZ\Lessons;


use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\POPO\WarmupItem;
use App\ReadXYZ\POPO\Warmup;

class Warmups
{
    private static Warmups $instance;

    private array $lessons = [];
    private array $shell;

    private function __construct()
    {
        $lessons_file = Util::getReadXyzSourcePath('resources/warmups.json');
        $str = file_get_contents($lessons_file, false, null);
        $this->shell = json_decode($str, true);
        foreach ($this->shell['lessons'] as $lesson) {
            $warmups = [];

            foreach ($lesson['warmups'] as $warmup) {
                $warmups[] = new WarmupItem($warmup['directions'], $warmup['parts']);
            }
            $lessonId = $lesson['lessonId'];
            $this->lessons[$lessonId] = new Warmup($lessonId, $lesson['instructions'] ?? '', $warmups );
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Warmups();
        }

        return self::$instance;
    }

    public function getLessonWarmup(string $lessonName): ?Warmup
    {
        return $this->lessons[$lessonName] ?? null;
    }
}
