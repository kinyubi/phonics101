<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\POPO\Animal;


class ZooAnimalsAlt
{
    use JsonTrait;

    protected static ZooAnimalsAlt $instance;

    private array $animals = [];
    /**
     * ZooAnimalsAlt constructor.
     * @throws PhonicsException
     */
    private function __construct()
    {
        $this->cachingEnabled = false;
        $this->baseConstruct('abc_zoo104.json', 'number');
        $this->baseMakeMap();
        $this->animals[0] = null;
        foreach ($this->persisted['map'] as $item) {
            $this->animals[$item->number] = new Animal($item->number, $item->name);
        }
    }

    public static function getInstance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new ZooAnimalsAlt();
        }

        return self::$instance;
    }


    /**
     * @return array for tic-tac-toe to random objects
     */
    public function get2AnimalObjects(): array
    {
        $count = count($this->animals);
        $first  = rand(0, $count - 1);
        $second = rand(0, $count - 1);
        while ($first == $second) {
            $second = rand(0, $count - 1);
        }
        return [$this->animals[$first], $this->animals[$second]];
    }

    /**
     * @param string $studentTag
     * @return array
     * @throws PhonicsException
     */
    public function getStudentAnimalSet(string $studentTag = ''): array
    {
        $count = count($this->animals);

        if ($studentTag == '') {
            $studentTag = Session::getStudentCode();
        }
        $index = (new StudentsData())->getAnimalIndex($studentTag);
        $animal1 = $this->animals[$index];
        $animal2 = (($index+2)>= $count) ? null : $this->animals[$index + 1];
        return [$animal1, $animal2];
    }

    public function getStudentZoo(int $length): array
    {
        return array_slice($this->animals,1, $length);
    }

    /**
     * @param string $studentTag
     * @return int
     * @throws PhonicsException
     */
    public function getIndex(string $studentTag = ''): int
    {
        if ($studentTag == '') {
            $studentTag = Session::getStudentCode();
        }
        return (new StudentsData())->getAnimalIndex($studentTag);
    }


}
