<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Enum\GeneratedType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\JSON\ZooAnimalsAlt;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\Page;
use Exception;
use stdClass;

class ZooTemplate
{

    private GeneratedType $generator;
    private string $studentCode;
    private ?stdClass $student;
    /**
     * ZooTemplate constructor.
     * @param string $studentTag
     * @throws PhonicsException
     */
    public function __construct(string $studentTag = '')
    {
        $studentsData = new StudentsData();
        if (empty($studentTag)) {
            $this->studentCode = Session::getStudentCode();
        } else {
            $this->studentCode = $studentsData->getStudentCode($studentTag);
        }
        $this->student = $studentsData->get($this->studentCode);
        $this->generator = new GeneratedType(GeneratedType::Zoo, $this->studentCode);
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @throws PhonicsException
     * @throws Exception
     */
    public function CreatePage()
    {
        $args        = [];
        $page        = new Page('My Animals');

        $zoo         = ZooAnimalsAlt::getInstance();


        $currentIndex  = $this->student->nextAnimal;
        $studentName = $this->student->studentName;
        $pretend     = (0 == $currentIndex);
        if ($pretend) {
            $currentIndex    = 103;
            $args['title'] = "Earn these animal prizes!";
        } else {
            $args['title'] = "$studentName's Animal Friends";
        }

        $args['hideTitleAnimals'] = true;
        $filePath = $this->generator->getFileName();
        $animals  = $zoo->getStudentZoo($currentIndex);
        $args['animals'] = $animals;
        $page->addArguments($args);

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
     * @throws PhonicsException
     */
    public function getZooUrl()
    {
        $this->createIfMissing();
        return $this->generator->getUrl();
    }

// ======================== PRIVATE METHODS =====================

    /**
     * @throws PhonicsException
     */
    private function createIfMissing(): void
    {
        $this->CreatePage();
        // if ( ! file_exists($this->generator->getFileName())) {
        //     $this->CreatePage();
        // }
    }

}
