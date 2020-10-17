<?php

namespace ReadXYZ\Twig;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lesson;

use ReadXYZ\Models\Identity;


/**
 * Class Twigs a collection of methods to render our site's pages.
 * Tab types: stretch (Intro), words (Write), practice, spinner(Spell), mastery, fluency, test.
 *
 * @package ReadXYZ\Twig
 */
class Twigs
{
    private static Twigs $instance;

    private TwigFactory $factory;
    private Identity $identity;

    private function __construct()
    {
        Util::sessionContinue();
        $this->identity = Identity::getInstance();
        $this->factory = TwigFactory::getInstance();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Twigs();
        }
        // These go here because student and current lesson can change

        return self::$instance;
    }


    /**
     * renders the 'Select Student' screen.
     *
     * @param array $allStudents a student list obtained from StudentTable::getInstance
     *
     * @return string the HTML to display the 'Select Student' screen
     */
    public function renderStudentList(array $allStudents): string
    {
        $page = new Page('Select a student');
        $studentLinks = [];
        foreach ($allStudents as $student) {
            $studentLinks[] = [
                'url' => Util::buildActionsLink('processStudentSelection', ['P1' => $student['studentID']]),
                'title' => ucfirst($student['enrollForm']['StudentName'])
            ];
        }

        return $page->simpleRender('login', 'selectStudent', ['studentLinks' => $studentLinks]);
    }



    /**
     * Returns the html for the login screen. If error message is specified, it will show up in a modal.
     * @param string $errorMessage
     * @return string
     */
    public function login(string $errorMessage = ''): string
    {
        $page = new Page('ReadXYZ Login');
        if ($errorMessage) {
            $page->addError($errorMessage);
        }

        return $page->simpleRender('login', 'login');
    }
}
