<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Secrets\Access;
use PDO;
use PDOException;
use Throwable;

class CompareLocalRemote
{

    private ?PDO $localPdo;
    private ?PDO $remotePdo;
    private bool $connectionFailed;

    public function __construct()
    {
        try {
            $this->localPdo = Access::connectLocalPdo();
            $this->remotePdo = Access::connectRemotePdo();
            $this->connectionFailed = (null === $this->localPdo) || (null === $this->remotePdo);
        } catch (Throwable $e) {
            echo "<p>Unexpected connection failure. " . $e->getMessage() . '</p>';
            $this->connectionFailed = true;
        }
    }

    public function analyze(): void
    {
        $this->columnAnalysis();
        $this->constraintAnalysis();
    }

    private function columnAnalysis(): void
    {
        echo "<h2>Column Analysis</h2>";
        $localTables = $this->getTableInfo($this->localPdo);
        $remoteTables = $this->getTableInfo($this->remotePdo);
        $this->compareTables($localTables, $remoteTables);
        $this->compareTables($remoteTables, $localTables);
    }

    private function constraintAnalysis(): void
    {
        echo "<h2>Constraint Analysis</h2>";
        $localConstraints = $this->getConstraintInfo($this->localPdo);
        $remoteConstraints = $this->getConstraintInfo($this->remotePdo);
        $this->compareConstraints($localConstraints, $remoteConstraints);
        $this->compareConstraints($remoteConstraints, $localConstraints);
    }

    private function getConstraintInfo(PDO $pdo): array
    {
        $constraints = [];
        $sql = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE ' .
               "TABLE_SCHEMA = 'readxyz0_phonics' ORDER BY TABLE_NAME, ORDINAL_POSITION";
        $statement = $pdo->prepare($sql);
        if (TRUE === $statement->execute()) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                foreach ($row as $key => $value) {
                    $constraints[$row['TABLE_NAME']][$row['COLUMN_NAME']][$key] = $value;
                }
            }
        }
        return $constraints;
    }

    private function getTableInfo(PDO $pdo): array
    {
        $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'readxyz0_phonics' ".
               ' ORDER BY TABLE_NAME, ORDINAL_POSITION';
        $statement = $pdo->prepare($sql);
        $tables = [];
        if (TRUE === $statement->execute()) {
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $tables[$row['TABLE_NAME']][$row['COLUMN_NAME']] = [];
                foreach ($row as $key => $value) {
                    $tables[$row['TABLE_NAME']][$row['COLUMN_NAME']][$key] = $value;
                }
            }
        }
        return $tables;
    }

    private function compareConstraints(array $first, array $second)
    {
        foreach ($first as $table => $info) {
            foreach ($info as $column => $data) {
                if (isset($second[$table][$column])) {
                    if (count($data)) {
                        foreach ($data as $key => $value) {
                            if ('CONSTRAINT_NAME' !== $key && $first[$table][$column][$key] !== $second[$table][$column][$key]) {
                                echo "<p>Column <strong>$column</strong> in table <strong>$table</strong> has differing characteristics for <strong>$key</strong> (" . $first[$table][$column][$key] . " vs. " . $second[$table][$column][$key] . ")</p>";
                            }
                        }
                    }
                } else {
                    echo "<p>Column <strong>$column</strong> in table <strong>$table</strong> is missing a constraint in the SECOND database!</p>";
                }
            }
        }
    }

    private function compareTables(array $first, array $second)
    {
        foreach ($first as $table => $info) {
            if ( ! isset($second[$table])) {
                echo "<p>Table <strong>$table</strong> does not exist in the SECOND database!</p>";
            } else {
                foreach ($info as $column => $data) {
                    if ( ! isset($second[$table][$column])) {
                        echo "<p>Column <strong>$column</strong> does not exist in table <strong>$table</strong> in the SECOND database!</p>";
                    } else {
                        if (count($data)) {
                            foreach ($data as $key => $value) {
                                if (empty($key)) continue;
                                if (($first[$table][$column][$key] ?? 'NOKEY') !== ($second[$table][$column][$key] ?? 'NOKEY')) {
                                    echo "<p>Column <strong>$column</strong> in table <strong>$table</strong> has differing characteristics for <strong>$key</strong> (" . ($first[$table][$column][$key] ?? 'NOKEY') . " vs. " . ($second[$table][$column][$key] ?? 'NOKEY') . ")</p>";
                                }
                            }
                        }
                    }
                }
            }
        }
    }


}
