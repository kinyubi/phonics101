<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;

abstract class AbstractData
{
    protected PhonicsDb $db;
    protected string $tableName;



    public function __construct(string $tableName)
    {
        // header is required because sendResponse fails without it
        $this->db = new PhonicsDb();
        $this->tableName = $tableName;
    }

    public function getCount(): int
    {
        $result = $this->db->queryAndGetCount("SELECT * FROM {$this->tableName}");
        return $result->wasSuccessful() ? $result->getResult() : 0;
    }

    public function truncate(): BoolWithMessage
    {
        if (runningStandalone()) {
            return $this->db->queryStatement("DELETE FROM {$this->tableName}");
        } else {
            throw new RuntimeException('May only be called in standalone environment.');
        }

    }

    protected function encodeJsonQuoted($object)
    {
        if ($object == null) return "NULL";
        $encode = json_encode($object, JSON_UNESCAPED_SLASHES);
        $fixed = str_replace("'", "\\'", $encode);
        return "'" . $fixed . "'";
    }

    /**
     * @param int $http_code  the http code we want the response to send
     * @param string $msg     the message we want the response to return (default: OK)
     */
    protected function sendResponse(int $http_code = 200, string $msg = 'OK'): void
    {
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode(['code' => $http_code, 'msg' => $msg]);
    }

    public function smartQuotes($value): string
    {

        if (!is_numeric($value) || '0' == $value[0]) {
            $value = "'" . $this->db->getConnection()->real_escape_string($value) . "'";
        }

        return $value;
    }

}
