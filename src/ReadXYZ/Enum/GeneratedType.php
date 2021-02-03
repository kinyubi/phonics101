<?php


namespace App\ReadXYZ\Enum;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\LessonsJson;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\TicTacToeTemplate;
use MyCLabs\Enum\Enum;

class GeneratedType extends Enum
{
    const TicTacToe     = 'ttt';
    const Zoo           = 'zoo';
    const LearningCurve = 'lcv';
    const TestCurve     = 'tcv';

    private array $extensionMap = [
        self::TicTacToe     => 'html',
        self::Zoo           => 'html',
        self::LearningCurve => 'png',
        self::TestCurve     => 'png'
    ];

    private string $studentCode;
    private string $url;
    private string $fileName;
    private string $globPattern;

    /**
     * GeneratedType constructor.
     * @param $value
     * @param string $studentTag
     * @throws PhonicsException
     */
    public function __construct($value, string $studentTag = '')
    {
        parent::__construct($value);
        $studentsData = new StudentsData();

        if (empty($studentTag)) {
            $code = Session::getStudentCode();
        } else {
            $code = $studentsData->getStudentCode($studentTag);
        }
        $this->studentCode = $code;
        $ordinal = 0;
        if ($value == GeneratedType::Zoo) {
            $ordinal = $studentsData->getAnimalIndex($code);
        } else {
            $lessonCode = Session::getCurrentLessonCode();
            if ($lessonCode) {
                $ordinal = LessonsJson::getInstance()->getOrdinal($lessonCode);
            }
        }
        $this->url = sprintf('/generated/%s_%03d_%s.%s', $code, $ordinal, $this->value, $this->getExtension());
        $this->globPattern = sprintf('/generated/%s_???_%s.%s', $code,  $this->value, $this->getExtension());
        $this->globPattern = Util::getPublicPath($this->globPattern);
        $this->fileName = Util::getPublicPath($this->url);
    }

// ======================== PUBLIC METHODS =====================
    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getStudentCode(): string
    {
        return $this->studentCode;
    }

    public function getGlobPattern(): string
    {
        return $this->globPattern;
    }

// ======================== PRIVATE METHODS =====================
    private function getExtension(): string
    {
        return $this->extensionMap[$this->value] ?? 'bad';
    }
}
