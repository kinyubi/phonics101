<?php


namespace App\ReadXYZ\Rest;


use App\ReadXYZ\Data\DbResult;
use App\ReadXYZ\Enum\MemberFieldTypes;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

class Membership
{

    private const API_KEY = 'cc8f9c8a006147d2e5d017c71907a05a';
    private const URL     = 'https://readxyz.com/?s2member_pro_remote_op=1';


// ======================== PUBLIC METHODS =====================

    /**
     * @param string $username
     * @param string $password
     * @return DbResult user_id on success, error message on failure
     */
    public function authorizeUser(string $username, string $password): DbResult
    {
        $inputData = [
            'op'      => 'auth_check_user',
            'api_key' => self::API_KEY,
            'data'    => [
                'user_login' => $username, //user_login or user_email value allowed
                'user_pass'  => $password
            ]

        ];
        return $this->wrapResult($this->post($inputData));
    }

    /**
     * @param stdClass|array $userObject . Required fields: user_login, user_email
     *   Optional fields: user_pass, first_name, last_name, s2member_notes, modify_if_login_exists, notification
     *   custom_fields[student1_firstname], custom_fields[student2_firstname], custom_fields[student3_firstname]
     * @return DbResult
     */
    public function createUser($userObject): DbResult
    {
        return $this->prepareAndPost($userObject, 'create_user');
    }

    /**
     * delete the specified user_id or user_login
     * @param string $user user_id or user_login to be deleted
     * @return DbResult
     */
    public function deleteUser(string $user): DbResult
    {
        $fieldName = is_numeric($user) ? 'user_id' : 'user_login';
        $inputData = [
            'op'      => 'auth_check_user',
            'api_key' => self::API_KEY,
            'data'    => [$fieldName => $user]

        ];
        return $this->wrapResult($this->post($inputData));
    }

    /**
     * @param string $member
     * @return stdClass if the valid field is true the other fields will be present
     */
    public function getUser(string $member): stdClass
    {
        $composite = Regex::parseCompositeEmail($member);
        if ($composite->success === true) {
            $fieldName = (empty($composite->student)) ? 'user_email' : 'user_login';
        } else {
            $fieldName = 'user_login';
        }
        $inputData = [
            'op'      => 'get_user',
            'api_key' => self::API_KEY,
            'data'    => [$fieldName => $member] //user_login, ID or user_email value allowed
        ];
        $result    = $this->post($inputData);
        return $this->extractUserInfo($result);

    }

    /**
     * @param stdClass $userObject . Required: Either user_login or user_id
     *   Optional: user_email, user_pass, first_name, last_name, s2member_notes, modify_if_login_exists, notification
     *   custom_fields[student1_firstname], custom_fields[student2_firstname], custom_fields[student3_firstname]
     * @return DbResult
     * @throws PhonicsException
     */
    public function modifyUser(stdClass $userObject): DbResult
    {
        if (empty($userObject->user_login) && empty ($userObject->user_id)) {
            throw new PhonicsException('Either user_login or user_id must be included in input object.');
        }
        if ( ! empty($userObject->user_login) && ! empty ($userObject->user_id)) {
            throw new PhonicsException('user_login and user_id cannot both be specified.');
        }
        return $this->prepareAndPost($userObject, 'modify_user');
    }

// ======================== PRIVATE METHODS =====================
    private function convertStdClassToArray($object): array
    {
        if ($object instanceof stdClass) {
            return (array)$object;
        } elseif (is_array($object) && isAssociative($object)) {
            return $object;
        } else {
            throw new PhonicsException("Input object must be stdClass object or associative array.");
        }
    }

    private function extractUserInfo(stdClass $result): stdClass
    {
        $valid        = empty($result->error);
        if (!$valid) return (object) ['valid' => false];

        $students     = [];
        $trainerType  = '';
        $active       = false;
        $customFields = $result->s2member_custom_fields ?? null;
        if ($customFields) {
            if ( ! empty($customFields->student1_firstname)) {
                $students[] = $customFields->student1_firstname;
            }
            if ( ! empty($customFields->student2_firstname)) {
                $students[] = $customFields->student2_firstname;
            }
            if ( ! empty($customFields->student3_firstname)) {
                $students[] = $customFields->student3_firstname;
            }
            if ( ! empty($customFields->trainer_type)) {
                $trainerType = $customFields->trainer_type;
            }
            if ( ! empty($customFields->active)) {
                $active = (1 == $customFields->active);
            }
        }
        return (object)[
            'valid'       => true,
            'userLogin'   => $result->data->user_login ?? '',
            'userEmail'   => $result->data->user_email ?? '',
            'displayName' => $result->data->display_name ?? '',
            'students'    => $students,
            'isAdmin'     => $trainerType == 'admin',
            'isStaff'     => in_array($trainerType, ['admin', 'staff']),
            'active'      => ($active == 1),
            'id'          => $result->ID,
            'role'        => $result->role,
        ];
    }

    /**
     * @param array $inputData
     * @return stdClass an associate array of results. If an error occurs the error string is in 'error'.
     */
    private function post(array $inputData): stdClass
    {
        $payload      = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => 's2member_pro_remote_op=' . urlencode(json_encode($inputData))
            ]
        ];
        $postData     = stream_context_create($payload);
        $receivedJson = trim(file_get_contents(self::URL, false, $postData));
        return json_decode($receivedJson, false);
    }

    /**
     * Convert stdClass to associative array
     * @param stdClass|array $object
     * @param string $operation
     * @return DbResult
     */
    private function prepareAndPost($object, string $operation): DbResult
    {
        $userArray = $this->convertStdClassToArray($object);
        $inputData = [
            'op'      => $operation,
            'api_key' => self::API_KEY,
            'data'    => $userArray
        ];
        return $this->wrapResult($this->post($inputData));
    }

    /**
     * @param stdClass|array $result
     * @return DbResult
     */
    private function wrapResult($result): DbResult
    {
        if (empty($result->error)) {
            return DbResult::goodResult($result);
        } else {
            return DbResult::badResult($result->error);
        }
    }
}
//  OTHER FIELDS
// 'modify_if_login_exists' => '0'|'1', if 'non-zero' and user already exists, update the existing account.
// 'user_pass' => '456DkaIjsd!', // Optional. Plain text Password. If empty, this will be auto-generated.
// 'first_name' => 'John', // Optional. First Name for the new User.
// 'last_name'  => 'Doe', // Optional. Last Name for the new User.
// 's2member_level' => '2', // Optional. Defaults to Level #0 (a Free Subscriber).
// 's2member_ccaps' => 'music,videos', // Optional. Comma-delimited list of Custom Capabilities.
// 's2member_auto_eot_time' any value that PHP's strtotime() function will understand (i.e., YYYY-MM-DD).
// 'custom_fields' An array of Custom Registration/Profile Field ID's, with associative values.
// 's2member_notes' => 'Administrative notation.
// 'notification' => '1' tells s2Member to email the new User/Member their Username/Password and notify site admin.
