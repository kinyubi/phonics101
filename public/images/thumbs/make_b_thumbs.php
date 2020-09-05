<?php

// This program goes through all of the b-*.jpg files
use ReadXYZ\Helpers\Util;

require dirname(dirname(__DIR__)) . '/autoload.php';

foreach (glob('../b-*.jpg') as $fileName) {
    $image = imagecreatefromjpeg($fileName);
    $x = imagesx($image);
    $y = Util::contains($fileName, 'b-d-p') ? imagesy($image) : 315;
    $im2 = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $x, 'height' => $y]);
    $im3 = imagescale($im2, 200);
    $output = str_replace('../','thumb_', $fileName);
    imagejpeg($im3, $output);
}
