<?php

use App\ReadXYZ\Helpers\Util;

require __DIR__ . '/autoload.php';

function deleteDirectory($dirPath) {
    if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object !="..") {
                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                    deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                } else {
                    $filename = $dirPath . DIRECTORY_SEPARATOR . $object;
                    $result = unlink($filename);
                    printf("%s %s deleted.\n", $filename, $result ? '' : 'NOT');
                }
            }
        }
        reset($objects);
        rmdir($dirPath);
    }
}


$cacheDir = Util::getReadXyzSourcePath('cache');
$generatedDir = Util::getPublicPath('generated');
$cutoff = time() - 3600; // 1 hour
$glob = glob($generatedDir . '/*.*');
$skipZoo = ($argv[1] ?? false) == 'nz';
foreach($glob as $file) {
    $isZoo = Util::contains('zoo', $file);
    if ($skipZoo) continue;
    if (filemtime($file) < $cutoff || $isZoo) {
        $result = unlink($file);
        printf("%s %s deleted.\n", $file, $result ? '' : 'NOT');
    }
}

$generatedOnly = ($argv[1] ?? false) == 'go';
if ($generatedOnly) exit();

deleteDirectory($cacheDir);
mkdir($cacheDir);
