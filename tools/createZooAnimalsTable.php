<?php

use App\ReadXYZ\Data\ZooAnimalData;

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

$friendlyName = [
    'elephant', 'monkey', 'tiger', 'panda', 'lion', 'bear', 'dog', 'cat', 'leopard', 'dolphin',
    'horse', 'wolf', 'salmon', 'jellyfish', 'penguin', 'cow', 'whale', 'giraffe', 'raccoon', 'goat',
    'rhino', 'otter', 'pig', 'hamster', 'hedgehog', 'pigeon', 'sheep', 'koala', 'fox', 'platypus',
    'hippo', 'gorilla', 'owl', 'chimpanzee', 'rat', 'lemur', 'toucan', 'beaver', 'frog', 'butterfly',
    'parrot', 'Red Panda', 'squirrel', 'zebra', 'rabbit', 'camel', 'flamingo', 'Polar Bear', 'seahorse', 'sloth',
    'skunk', 'starfish', 'swan', 'Sugar Glider', 'snail', 'duck', 'Puffer Fish', 'shark', 'eagle', 'crab',
    'tortoise', 'ladybug', 'turkey', 'snake', 'cougar', 'chicken', 'crocodile', 'ostrich', 'peacock', 'panther',
    'seal', 'porcupine', 'anteater', 'bee', 'hummingbird', 'mouse', 'octopus', 'kangaroo', 'bison', 'kiwi',
    'Guinea Pig', 'llama', 'cheetah', 'turtle', 'walrus', 'yak', 'Arctic Fox', 'orca', 'deer', 'shrimp',
    'jaguar', 'emu', 'toad', 'stingray', 'beetle', 'lobster', 'scorpion', 'reindeer', 'spider', 'mantis'
];

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

$animalData = new ZooAnimalData();
$count = $animalData->getCount();
if ($count > 0) {
    $result = $animalData->truncate();
    if ($result->failed()) {
        exit( $result->getErrorMessage());
    }
}

for ($i=0; $i<100; $i++) {
    $result = $animalData->insert($animals[$i], ucfirst($friendlyName[$i]));
    if ($result->failed()) exit($result->getErrorMessage());
}

$count = $animalData->getCount();
$expected = count($animals);
if ($count != $expected) {
    exit("Expected to insert $expected but $count records actually inserted");
}
