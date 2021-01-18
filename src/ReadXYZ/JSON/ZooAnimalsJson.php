<?php


namespace App\ReadXYZ\JSON;

use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class ZooAnimalsJson. fields(animalCode, fileName, grayFileName, friendlyName). associatedLesson(not used)
 * @package App\ReadXYZ\JSON
 */
class ZooAnimalsJson
{
    use JsonTrait;

    protected static ZooAnimalsJson   $instance;
    protected array                   $keys;

    /**
     * ZooAnimalsJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('zoo_animals.json', 'animalCode');
        $this->baseMakeMap();
        $this->keys = array_keys($this->map);
    }

// ======================== PUBLIC METHODS =====================
    /**
     * return 2 ZooAnimal stdClass objects (animalCode, fileName, grayFileName, friendlyName).
     * @return array
     */
    public function get2Random(): array
    {
        $first  = rand(0, 99);
        $second = rand(0, 99);
        while ($first == $second) {
            $second = rand(0, 99);
        }
        $firstKey  = $this->keys[$first];
        $secondKey = $this->keys[$second];
        return [$this->map[$firstKey], $this->map[$secondKey]];
    }

}
