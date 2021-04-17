<?php


namespace App\ReadXYZ\POPO;


class Animal
{
    public string $animalCode;
    public string $fileName;
    public string $grayFileName;
    public string $awardFileName;
    public string $friendlyName;
    public int    $ordinal;

    public function __construct(int $number, string $name) {
        $this->animalCode = $name;
        $this->fileName = "/images/animals/numbered150/$number.png";
        $this->grayFileName = "/images/animals/gray150/$number.png";
        $this->awardFileName = "/images/mp4/$number.mp4";
        $this->friendlyName = ucwords($name);
        $this->ordinal = $number;
    }
}
