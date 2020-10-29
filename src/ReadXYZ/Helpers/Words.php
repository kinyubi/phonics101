<?php

namespace App\ReadXYZ\Helpers;

use Throwable;

define('W_RANDOM', 1 << 8); //256
define('W_REVERSE', 1 << 9);
define('W_SINGLE', 1 << 10);
define('W_DOUBLE', 2 << 10);
define('W_TRIPLE', 3 << 10);
define('W_STYLE_NONE', 1 << 12);
define('W_STYLE_SIMPLE', 2 << 12);
define('W_STYLE_FULL', 3 << 12);
define('W_3_COLUMN', 1 << 14);

class Words
{
    public static function randomMinRepeats(array $wordSets, int $minSets, int $maxSets): array
    {
        $count = count($wordSets);
        $offset = 0;
        if ($count > $maxSets) {
            try {
                $offset = random_int(0, $count - $maxSets);
            } catch (Throwable $ex) {
            }
            $workingWordSets = array_slice($wordSets, $offset, $maxSets);
        } elseif ($count < $minSets) {
            $workingWordSets = $wordSets;
            for ($i = 0; $i < $minSets; ++$i) {
                $workingWordSets[] = $wordSets[$i % $count];
            }
        }
        shuffle($workingWordSets);
        shuffle($workingWordSets);
        shuffle($workingWordSets);
        return $workingWordSets;
    }
}
