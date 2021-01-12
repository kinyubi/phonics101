<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Helpers\PhonicsException;


class OldUserMastery extends AbstractJson
{
    private array $wordMastery;
    /**
     * builds an object with the student data we'll need for abc_students, abc_trainers and abc_studentLesson
     * abcStudentsFromOldStudents constructor.
     * @throws PhonicsException
     */
    public function __construct()
    {
        parent::__construct('abc_usermastery.json');
        $this->wordMastery = $this->importDataAsAssociativeArray();
    }

    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }

    /**
     * @throws PhonicsException
     */
    public function populateAbcWordMastery(): void
    {
        $wordMasteryTable = new WordMasteryData();
        if (0 != $wordMasteryTable->getCount()) {
            throw new PhonicsException("This function is only available is abc_word_mastery is empty.");
        }
        $studentsTable = new StudentsData();
        $currentStudent = '';
        $currentStudentValid = false;
        $newCode = '';
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
