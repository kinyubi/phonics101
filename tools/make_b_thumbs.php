<?php

// This program goes through all of the b-*.jpg files
use App\ReadXYZ\Helpers\Util;

require 'autoload.php';

foreach (glob('../b-*.jpg') as $fileName) {
    $image = imagecreatefromjpeg($fileName);
    $x = imagesx($image);
    $y = Util::contains('b-d-p', $fileName) ? imagesy($image) : 315;
    $im2 = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $x, 'height' => $y]);
    $im3 = imagescale($im2, 200);
    $output = str_replace('../','thumb_', $fileName);
    imagejpeg($im3, $output);
}
