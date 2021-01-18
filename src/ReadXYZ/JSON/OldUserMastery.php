<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Helpers\PhonicsException;


class OldUserMastery
{
    use JsonTrait;

    protected static OldUserMastery   $instance;
    private array                     $wordMastery;

    /**
     * builds an object with the student data we'll need for abc_students, abc_trainers and abc_studentLesson
     * abcStudentsFromOldStudents constructor.
     * @throws PhonicsException
     */
    public function __construct()
    {
        $this->baseConstruct('abc_usermastery.json');
        $this->baseMakeMap();
        $this->wordMastery = $this->importDataAsAssociativeArray();
    }

// ======================== PUBLIC METHODS =====================
    /**
     * populates word mastery from mastery words on old version of program
     * @throws PhonicsException
     */
    public function populateAbcWordMastery(): void
    {
        $wordMasteryTable = new WordMasteryData();
        if (0 != $wordMasteryTable->getCount()) {
            throw new PhonicsException("This function is only available is abc_word_mastery is empty.");
        }
        $studentsTable       = new StudentsData();
        $currentStudent      = '';
        $currentStudentValid = false;
        $newCode             = '';
        foreach ($this->wordMastery as $record) {
            $id = $record->studentID;
            if ($id != $currentStudent) {
                $currentStudent      = $id;
                $newCode             = $studentsTable->newStudentCode($id);
                $currentStudentValid = $studentsTable->doesStudentExist($newCode);
            }
            if ( ! $currentStudentValid) {
                continue;
            }
            $wordMasteryTable->add($newCode, $record->word);
        }
    }
}
