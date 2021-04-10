<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Models\Session;

class ZooAnimalsAlt
{
    protected static ZooAnimalsAlt $instance;

    protected array $animals = [
            '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20',
            '21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38',
            '39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56',
            '57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74',
            '75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92',
            '93','94','95','96','97','98','99','100','101','102','103','104'
    ];

    protected array $map = [];

    public static function getInstance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new ZooAnimalsAlt();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->makeAllObjects();
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
        return [$this->map[$first], $this->map[$second]];
    }

    public function getAnimalNames(): array
    {
        return $this->animals;
    }

    public function getAllAnimals(): array
    {
        return $this->map;
    }

    public function getStudentAnimalSet(string $studentTag = ''): array
    {
        $count = count($this->animals);

        if ($studentTag == '') {
            $studentTag = Session::getStudentCode();
        }
        $index = (new StudentsData())->getAnimalIndex($studentTag);
        $animal1 = $this->makeObject($index );
        $animal2 = (($index+2)>= $count) ? null : $this->makeObject($index + 1);
        return [$animal1, $animal2];
    }

    public function getStudentZoo(int $length): array
    {
        return array_slice($this->map,0, $length);
    }

    public function getIndex(string $studentTag = ''): int
    {
        if ($studentTag == '') {
            $studentTag = Session::getStudentCode();
        }
        return (new StudentsData())->getAnimalIndex($studentTag);
    }

    private function makeObject(int $number): object
    {
        $number = clamp($number, 0, 104);
        $strVal = strval($number);
        return (object) [
            'animalCode' => $strVal,
            'fileName' => "/images/animals/numbered150/$strVal.png",
            'grayFileName' => "/images/animals/gray150/$strVal.png",
            'friendlyName' => "My animal",
            'ordinal' => $number
        ];

    }

    private function makeAllObjects()
    {
        $count = count($this->animals);
        for ($i = 1; $i <= $count; $i++) {
            $this->map[] = $this->makeObject($i);
        }
    }

}
