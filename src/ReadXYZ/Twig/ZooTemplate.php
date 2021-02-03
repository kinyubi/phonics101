<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Enum\GeneratedType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\ZooAnimalsJson;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\Page;
use Exception;

class ZooTemplate
{

    private GeneratedType $generator;

    /**
     * ZooTemplate constructor.
     * @param string $studentTag
     * @throws PhonicsException
     */
    public function __construct(string $studentTag = '')
    {
        if (empty($studentTag)) {
            $this->studentCode = Session::getStudentCode();
        } else {
            $this->studentCode = (new StudentsData())->getStudentCode($studentTag);
        }
        $this->generator = new GeneratedType(GeneratedType::Zoo, $this->studentCode);
    }

// ======================== PUBLIC METHODS =====================
    /**
     * @throws PhonicsException
     * @throws Exception
     */
    public function createZooPage()
    {
        $args        = [];
        $page        = new Page('My Animals');
        $studentName = Session::getStudentName();
        $studentCode = Session::getStudentCode();
        $zoo         = ZooAnimalsJson::getInstance();

        $studentData = new StudentsData();
        $lastAnimal  = $studentData->getAnimalIndex($studentCode);
        $pretend     = (0 == $lastAnimal);
        if ($pretend) {
            $lastAnimal    = random_int(10, 75);
            $args['title'] = "Earn these animal prizes!";
        } else {
            $args['title'] = "$studentName's Animal Friends";
        }


        $args['hideTitleAnimals'] = true;
        $url                      = sprintf('/generated/%s_zoo.html', $studentCode);
        $filePath                 = Util::getPublicPath($url);
        $animals                  = array_slice($zoo->getStudentZoo(), 0, $lastAnimal + 1);
        shuffle($animals);
        $args['animals'] = $animals;
        $page->addArguments($args);

        $this->deleteExisting($this->generator->getFileName());
        $html = $page->getHtml('zoo_animals.html.twig');
        file_put_contents($filePath, $html);
    }

    public function deleteExisting(string $exception = ''): void
    {
        $files = glob($this->generator->getGlobPattern());
        foreach ($files as $file) {
            if ($file != $exception) {
                unlink($file);
            }
        }
    }

    /**
     * @return string
     */
    public function getZooUrl()
    {
        $this->createIfMissing();
        return $this->generator->getUrl();
    }

// ======================== PRIVATE METHODS =====================
    private function createIfMissing(): void
    {
        if ( ! file_exists($this->generator->getFileName())) {
            $this->createZooPage();
        }
    }

}
