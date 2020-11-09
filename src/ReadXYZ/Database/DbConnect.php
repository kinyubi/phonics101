<?php

namespace App\ReadXYZ\Database;

use Exception;
use mysqli;
use App\ReadXYZ\Secrets\Access;

class DbConnect
{
    public mysqli $dbConnector; // a connection

    /**
     * standard constructor.
     *
     * @param mysqli|null $conn for testing purposes you can specify an alternate db connection
     *
     * @throws Exception if unable to connect
     */
    public function __construct(mysqli $conn = null)
    {
        if ($conn && defined('UNIT_TESTING')) {
            $this->dbConnector = $conn;
        } else {
            $this->dbConnector = Access::dbConnect();
            if (mysqli_connect_errno()) {
                throw new Exception('Cannot connect to MySQL server:' . mysqli_connect_errno());
            }
        }
    }

    /**
     * @param string $query the query string
     *
     * @return array the results of the query
     *
     * @throws Exception if SQL error encountered
     */
    public function query(string $query): array
    {
        //echo 'query:',$query,'<br>';
        $data = [];
        if ($result = mysqli_query($this->dbConnector, $query)) {
            /* fetch associative array */
            if (!is_bool($result)) { // was probably a statement
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }
                /* free result set */
                mysqli_free_result($result);
            }
            //if(isset($data[0]))
            //    echo serialize($data[0]),'<br>';
            //echo '<br>';
        } else {
            $error = mysqli_error($this->dbConnector);
            $message = "$error : query string: $query";
            throw new Exception($message);
        }

        return $data;
    }


    public function fetch_array($query)
    {
        return $this->query($query);
    }

    public function first_cell($query)
    {
        $result = $this->query($query);
        if (is_array($result) && array_key_exists(0, $result)) {
            return $result[0];
        }

        return null;
    }

    public function insert_id()
    {
        $id = $this->first_cell('SELECT LAST_INSERT_ID() as id');

        return $id;
    }

    public function close()
    {
        @mysqli_close($this->dbConnector);
        //unset($this);
    }
}
