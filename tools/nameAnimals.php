<?php

require 'autoload.php';

$animals = [
    'elephant', 'monkey', 'tiger', 'panda', 'lion', 'bear', 'dog', 'cat', 'leopard', 'dolphin',
    'horse', 'wolf', 'salmon', 'jellyfish', 'penguin', 'cow', 'whale', 'giraffe', 'raccoon', 'goat',
    'rhino', 'otter', 'pig', 'hamster', 'hedgehog', 'pigeon', 'sheep', 'koala', 'fox', 'platypus',
    'hippo', 'gorilla', 'owl', 'chimpanzee', 'rat', 'lemur', 'toucan', 'beaver', 'frog', 'butterfly',
    'parrot', 'redpanda', 'squirrel', 'zebra', 'rabbit', 'camel', 'flamingo', 'polarbear', 'seahorse', 'sloth',
    'skunk', 'starfish', 'swan', 'sugarglider', 'snail', 'duck', 'pufferfish', 'shark', 'eagle', 'crab',
    'tortoise', 'ladybug', 'turkey', 'snake', 'cougar', 'chicken', 'crocodile', 'ostrich', 'peacock', 'panther',
    'seal', 'porcupine', 'anteater', 'bee', 'hummingbird', 'mouse', 'octopus', 'kangaroo', 'bison', 'kiwi',
    'guineapig', 'llama', 'cheetah', 'turtle', 'walrus', 'yak', 'arcticfox', 'orca', 'deer', 'shrimp',
    'jaguar', 'emu', 'toad', 'stingray', 'beetle', 'lobster', 'scorpion', 'reindeer', 'spider', 'mantis'
];

$animalDir = __DIR__ . '/animal_icons';
$namedDir = "$animalDir/named";
$grayDir = "$animalDir/gray";
for ($i=0; $i<100; $i++) {
    $plus1 = strval($i+ 1);
    $originalName = "$animalDir/$plus1.jpg";
    $newName = "$namedDir/{$animals[$i]}.jpg";
    $originalGrayName = "$animalDir/{$plus1}g.jpg";
    $newGrayName = "$grayDir/{$animals[$i]}_gray.jpg";
    if (!file_exists($namedDir)) mkdir($namedDir);
    if (!file_exists($grayDir)) mkdir($grayDir);
    if (file_exists($originalName)) {
        $result = copy($originalName, $newName);
        if ($result === false) printf("$originalName copy failed.\n");
    } else {
        printf("$originalName does not exist\n");
    }

    if (file_exists($originalGrayName)) {
        $result = copy($originalGrayName, $newGrayName);
        if ($result === false) printf("$originalGrayName copy failed.\n");
    } else {
        printf("$originalGrayName does not exist\n");
    }
}
