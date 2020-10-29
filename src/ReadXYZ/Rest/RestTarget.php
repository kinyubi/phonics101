<?php


namespace App\ReadXYZ\Rest;


use Exception;
use App\ReadXYZ\Helpers\Util;

/* HTTP Codes
200 - OK
400 - Bad request
401 - Unauthorized (authentication required but not provided)
403 - Forbidden (valid data but unable to process. Do not repeat request)
404 - Not found (resource not found)
500 - Internal Server Error
501 - Not implemented (Server doesn't recognize method or can't fulfill request)
505 - HTTP Version not supported
*/

class RestTarget
{
    /**
     * send a json string to stdout with the fields 'code', 'msg' and ''canlogin'.
     *
     * @param int $httpCode
     * @param string $msg if http_code not 200, an explanation of the problem
     * @param bool $canLogin
     * @return string a json-encoded string the caller should echo
     */
    protected function getRestResponse(int $httpCode = 200, string $msg = 'OK', bool $canLogin = false): string
    {
        http_response_code($httpCode);
        $canString = $canLogin ? 'YES' : 'NO';
        return json_encode(['code' => $httpCode, 'msg' => $msg, 'canlogin' => $canString]);
    }

    /**
     * Creates a StdClass basicResponse object with the fields 'success', 'code', 'msg' and 'result'
     * from the inputs and returns it.
     *
     * @param bool success   Was the rest call successful
     * @param int code      The http code we want to return (default 200)
     * @param string msg       The message to return (default OK)
     * @param mixed result    The result. Type depends
     *
     * @return string a JSON-encoded string with the fields 'success', 'msg' and 'result'
     */
    protected function basicResponse(bool $success = true, int $code = 200, string $message = 'OK', $result = null): string
    {
        return json_encode(['success' => $success, 'code' => $code, 'msg' => $message, 'result' => $result]);
    }

    /**
     * Executes a MySQLi query and returns a StdClass object with result set to true or false
     * depending on whether the query returned any rows.
     * @param string query The SQL query to be executed
     *
     * @return bool returns true if the sql query returns something other than empty
     *
     * @throws Exception if query command unexpectedly fails
     */
    protected function sqlExists($query): bool
    {
        $conn = Util::dbConnect();
        if ($result = $conn->query($query)) {
            $exists = ($result->num_rows > 0);
            $result->close();
            $conn->close();
            return $exists;
        } else {
            throw new Exception("Query unexpectedly failed ({$conn->error}): $query");
        }
    }


}
