<?php

use App\ReadXYZ\Helpers\Util;

require __DIR__ . "/autoload.php";

$inputFile = Util::getReadXyzSourcePath('JSON/data/abc_lessons.json');
if (! file_exists($inputFile)) exit("$inputFile not found.");
$outputFile = Util::getReadXyzSourcePath('JSON/data/new_abc_lessons.json');

$inCount = $outCount = 0;

$inHandle  = fopen($inputFile, 'r');
$outHandle = fopen($outputFile, 'w');
if (! $inHandle) exit("Error opening $inputFile.");
if (! $outputFile) exit("Error opening $outputFile.");

$previousWasSuffix = false;
while (($line = fgets($inHandle)) !== false) {
    $inCount++;
    // if (Util::contains('ignoreMe', $line)) continue;
    // if (Util::contains('"visible"', $line)) continue;
    // if (Util::contains('"supplementalWordList": ""', $line)) continue;
    if ($previousWasSuffix && Util::contains(',', $line)) {
        $line = str_replace(',', '', $line);
    }
    // if (Util::contains('"stretchList"', $line)) {
    //     $extraLine = "                \"soundLetters\": \"abcdefghijklmnopqrstuvwxyz\",\n";
    //     fputs($outHandle, $extraLine);
    //     $outCount++;
    // }
    fputs($outHandle, $line);
    $previousWasSuffix =  (Util::contains('"suffixList":', $line));
    $outCount++;
}

fclose($inHandle);
fclose($outHandle);

printf("$inCount lines read. $outCount lines written.");
