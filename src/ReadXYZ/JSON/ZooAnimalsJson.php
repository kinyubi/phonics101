<?php


namespace App\ReadXYZ\JSON;

use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;

/**
 * Class ZooAnimalsJson. fields(animalCode, fileName, grayFileName, friendlyName). associatedLesson(not used)
 * @package App\ReadXYZ\JSON
 */
class ZooAnimalsJson
{
    use JsonTrait;

    protected static ZooAnimalsJson   $instance;
    protected static array                   $animals = [
        'dog', 'cat', 'elephant', 'monkey', 'tiger', 'panda', 'lion', 'bear', 'leopard', 'dolphin',
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
    protected array                   $keys;

    /**
     * ZooAnimalsJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('abc_zoo_animals.json', 'animalCode');
        $this->baseMakeMap();
        $this->keys = array_keys($this->persisted['map']);
    }

// ======================== STATIC METHODS =====================
    public static function get2RandomAnimalNames(): array
    {
        $first  = rand(0, 99);
        $second = rand(0, 99);
        while ($first == $second) {
            $second = rand(0, 99);
        }
        return [self::$animals[$first], self::$animals[$second]];
    }

    public static function getAnimalNames(): array
    {
        return self::$animals;
    }

// ======================== PUBLIC METHODS =====================
    /**
     * return 2 ZooAnimal stdClass objects (animalCode, fileName, grayFileName, friendlyName).
     * @param string $animal1
     * @param string $animal2
     * @return array
     */
    public function get2AnimalObjects(string $animal1='', string $animal2=''): array
    {
        if ((empty($animal1) || empty($animal2))) {
            $keys    = self::get2RandomAnimalNames();
            $animal1 = $keys[0];
            $animal2 = $keys[1];
        }
        return [$this->persisted['map'][$animal1], $this->persisted['map'][$animal2]];
    }

    /**
     * Returns two objects: animal last earned and the next animal to be earned.
     * @param string $studentTag
     * @return array
     * @throws PhonicsException
     */
    public function getStudentAnimalSet(string $studentTag = ''): array
    {
        if ($studentTag == '') {
            $studentTag = Session::getStudentCode();
        }
        $index = (new StudentsData())->getAnimalIndex($studentTag);
        $key   = self::$animals[$index];
        if ($index == 99) {
            return [$this->persisted['map'][$key], null];
        } else {
            $key2 = self::$animals[$index + 1];
            return [$this->persisted['map'][$key], $this->persisted['map'][$key2]];
        }
    }

    /**
     * returns
     * @param int $length
     * @return array
     * @throws PhonicsException
     */
    public function getStudentZoo(int $length=0): array
    {
        if ($length == 0) {
            $studentTag = Session::getStudentCode();
            $length = (new StudentsData())->getAnimalIndex($studentTag) + 1;
        }

        return array_slice($this->persisted['objects'],0, $length);
    }

}
