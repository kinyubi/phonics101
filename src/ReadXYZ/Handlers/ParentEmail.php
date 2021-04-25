<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\LessonListTemplate;
use \Throwable;

class ParentEmail extends AbstractHandler
{
    private static string $email = <<<EOT
    <html lang="en">
    <head>
    <title>Congratulations</title>
    <link rel="stylesheet" href="{{phonicsUrl}}/css/bootstrap4-custom/bootstrap.min.css">
    <link rel="stylesheet" href="{{phonicsUrl}}/css/colorbox/colorbox.css" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{phonicsUrl}}/css/phonics.css">
    </head>
    <body>
    <div class="container-fluid bg-white" >
        <div class="row  d-flex flex-nowrap  m-2 justify-content-between">
            <div class="col-8" style="font-size: 1em;font-family:'Comic Sans MS',serif;">
                <p class="my-2 font-weight-bold">Hi {{parent}},</p>
                <p>Today {{student}} worked on the lesson <span>"{{lesson}}"</span>.</p><br>
                <p class="font-weight-bold">The practice list is:</p>
                <ul class="ml-3">{{words}}</ul>
                <br><br>
<!--                <p class="font-weight-bold">The fluency passages are:</p>-->
                <ul>{{sentences}}</ul>
                <br>
            </div>
        </div>
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
            $studentCode = $_REQUEST['studentCode'];
            $emailOnFile = $studentsData->getParentEmail($studentCode);
            $to          = $_REQUEST['parentEmail'];
            $subject     = 'Congratulations';
            $parentName  = $_REQUEST['parentName'];
            $studentName = $_REQUEST['studentName'];
            $lessonName  = $_REQUEST['lessonName'];
            $words       = $_REQUEST['words'];
            $sentences   = $_REQUEST['sentences'];
            $wordlist    = '';
            foreach ($words as $word) {
                $wordlist .= "<li class='ml-3'>$word</li>";
            }
            $sentenceList = '';
            foreach ($sentences as $sentence) {
                $sentenceList .= "<li class='ml-3 mt-2'>$sentence</li>";
            }
            $phonicsUrl = Util::getPublicPath();
            $vars       = ['{{phonicsUrl}}', '{{parent}}', '{{student}}', '{{lesson}}', '{{words}}', '{{sentences}}'];
            $values     = [$phonicsUrl, $parentName, $studentName, $lessonName, $wordlist, $sentenceList];
            $replaceCount = 0;
            $message    = str_replace($vars, $values, self::$email, $replaceCount);
            $headers    = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
            $headers    .= 'From: <noreply@readxyz.com>' . "\r\n";
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
