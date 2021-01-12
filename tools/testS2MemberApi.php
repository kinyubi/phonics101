<?php

use App\ReadXYZ\Rest\Membership;

require 'autoload.php';


$membership = new Membership();
// $erinEmail  = $membership->getUser('erinweinman@gmail.com');
// $erinComposite       = $membership->getUser('erinweinman@gmail.com-Charlotte');
$uc     = $membership->getUser('jamiehafling@aol.com-Jojo');
$lc = $membership->getUser('jamiehafling@aol.com-jojo');
//$lisaComp = $membership->getUser('carlbaker');
// print_r($erinEmail);
// print_r($erinComposite);
// print_r($erinLowerCase);
// print_r($lisaEmail);
print_r($uc);
print_r($lc);

/*
 AFTER EXTRACT USER INFO
(
    [valid] => 1
    [userLogin] => carlbaker
    [userEmail] => carlbaker@gmail.com
    [displayName] => Carl Baker
    [students] => Array
        (
            [0] => George
            [1] => Henry
        )

    [isAdmin] => 1
    [isStaff] => 1
    [active] => 1
    [id] => 26
    [role] => administrator
)
stdClass Object
(
    [valid] => 1
    [userLogin] => lisal
    [userEmail] => lisamichelle@gmail.com
    [displayName] => lisal
    [students] => Array
        (
        )

    [isAdmin] =>
    [isStaff] =>
    [active] =>
    [id] => 1
    [role] => administrator
)

BEFORE EXTRACT USER INFO
 (
     [success:App\ReadXYZ\Data\DbResult:private] => 1
     [result:App\ReadXYZ\Data\DbResult:private] => stdClass Object
         (
             [ID] => 26
             [role] => administrator
             [level] => 12
             [ccaps] => Array
                 (
                 )

             [data] => stdClass Object
                 (
                     [ID] => 26
                     [user_login] => carlbaker
                     [user_nicename] => carlbaker
                     [user_email] => carlbaker@gmail.com
                     [user_url] =>
                     [user_registered] => 2020-12-01 22:34:20
                     [user_activation_key] => 1606862060:$P$B06jqixPREjmlE84g/03Ixksj2ENDt1
                     [user_status] => 0
                     [display_name] => Carl Baker
                 )

             [s2member_custom_fields] => stdClass Object
                 (
                     [student1_firstname] => George
                     [student2_firstname] => Henry
                     [student3_firstname] =>
                     [trainer_type] => admin
                     [active] => 1
                 )

             [s2member_paid_registration_times] => stdClass Object
                 (
                     [level] => 1606862060
                     [level12] => 1606862060
                 )

             [s2member_file_download_access_log] =>
         )

     [message:App\ReadXYZ\Data\DbResult:private] =>
 )

(
    [success:App\ReadXYZ\Data\DbResult:private] => 1
    [result:App\ReadXYZ\Data\DbResult:private] => stdClass Object
        (
            [ID] => 1
            [role] => administrator
            [level] => 12
            [ccaps] => Array
                (
                )

            [data] => stdClass Object
                (
                    [ID] => 1
                    [user_login] => lisal
                    [user_nicename] => lisal
                    [user_email] => lisamichelle@gmail.com
                    [user_url] =>
                    [user_registered] => 2019-04-11 02:42:39
                    [user_activation_key] =>
                    [user_status] => 0
                    [display_name] => lisal
                )

            [s2member_originating_blog] =>
            [s2member_subscr_gateway] =>
            [s2member_subscr_id] =>
            [s2member_custom] =>
            [s2member_registration_ip] => 96.19.163.218
            [s2member_notes] =>
            [s2member_auto_eot_time] =>
            [s2member_custom_fields] =>
            [s2member_paid_registration_times] => stdClass Object
                (
                    [level] => 1605398674
                    [level4] => 1605398674
                )

            [s2member_file_download_access_log] =>
        )

    [message:App\ReadXYZ\Data\DbResult:private] =>
)

 */
