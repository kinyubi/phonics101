<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Enum\GeneratedType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\Page;
use Exception;
use stdClass;

class AwardTemplate
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
        $studentCode = Session::getStudentCode();
        $this->generator = new GeneratedType(GeneratedType::Award, $studentCode);
        $studentsData = new StudentsData();
        $this->student = $studentsData->get($studentCode);
    }

    // ======================== PUBLIC METHODS =====================

        /**
         * @throws PhonicsException
         * @throws Exception
         */
        public function createPage()
        {
            $args        = [];
            $page        = new Page('Award Ceremony');

            $nextAnimal  = $this->student->nextAnimal + 2;
            $nextStr = strval($nextAnimal);
            if (file_exists(Util::getPublicPath("images/mp4/$nextStr.mp4"))) {
                $url = "/images/mp4/$nextAnimal.mp4";
            } else {
                $url = "/images/mp4/1.mp4";
            }
            $args['title'] = "Award Ceremony";
            $args['mp4'] = $url;
            $args['callback'] = 'http://' . $_SERVER['HTTP_HOST'] . '/handler/award';
            $filePath = $this->generator->getFileName();
            $page->addArguments($args);

            $html = $page->getHtml('awards.html.twig');
            file_put_contents($filePath, $html);
        }

        public function getUrl()
        {
            $this->createPage();
            return $this->generator->getUrl();
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

}
