<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\LessonListTemplate;
use App\ReadXYZ\Data\TrainersData;
use \Throwable;

class ParentEmail extends AbstractHandler
{
    private static string $email = <<<EOT
    <html lang="en">
    <head>
        <style>
            @font-face {
                font-family: "Muli";
                src: url(https://fonts.googleapis.com/css?family=Muli);
            }
        </style>
    </head>
    <body>
    <div class="container" style="width: 100%;">
        <pre class="email-content" style="font-family: sans-serif; font-size: 1.2em;">{{emailBody}}</pre>
    </div>
EOT;

    /**
     * @throws PhonicsException
     */
    public static function handlePost(): void
    {
        $studentsData = new StudentsData();

        self::fullLocalErrorReportingOn();
        try {
            $trainerCode = $_REQUEST['trainerCode'];
            $studentCode = $_REQUEST['studentCode'];
            $emailOnFile = $studentsData->getParentEmail($studentCode);
            $to          = $_REQUEST['parentEmail'];
            $subject     = 'Lesson Summary';
            $emailBody   = $_REQUEST['emailBody'];
            $trainersData = new TrainersData();
            $trainer = $trainersData->get($trainerCode);

            // $parentName  = $_REQUEST['parentName'];
            // $studentName = $_REQUEST['studentName'];
            // $lessonName  = $_REQUEST['lessonName'];
            // $words       = $_REQUEST['words'];
            // $sentences   = $_REQUEST['sentences'];
            // $wordlist    = '';
            // foreach ($words as $word) {
            //     $wordlist .= "<li class='ml-3'>$word</li>";
            // }
            // $sentenceList = '';
            // foreach ($sentences as $sentence) {
            //     $sentenceList .= "<li class='ml-3 mt-2'>$sentence</li>";
            // }
            $phonicsUrl = Util::getPublicPath();
            // $vars       = ['{{phonicsUrl}}', '{{parent}}', '{{student}}', '{{lesson}}', '{{words}}', '{{sentences}}'];
            // $values     = [$phonicsUrl, $parentName, $studentName, $lessonName, $wordlist, $sentenceList];
            // $replaceCount = 0;
            $vars = ['{{phonicsUrl}}', '{{emailBody}}'];
            $values = [$phonicsUrl, $emailBody];
            $message    = str_replace($vars, $values, self::$email, $replaceCount);
            $headers    = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
            $headers    .= 'From: <noreply@readxyz.com>' . "\r\n";
            $headers    .= 'Cc: ' . $trainer->userName . '\r\n';
            if (empty($to)) {
                Util::redBox("An email address must be provided.");
            } else {
                if ($emailOnFile != $to) {
                    $studentsData->updateParentEmail($studentCode, $to);
                }
                $status = mail($to, $subject, $message, $headers);
                if (!$status) {
                    Util::redBox("Error sending email.");
                }
                self::fullLocalErrorReportingOff();
                   $lessonList = new LessonListTemplate();
                   $lessonList->display();
            }

        } catch (Throwable $ex) {
            Util::redBox($ex->getMessage());
        }
    }
}
