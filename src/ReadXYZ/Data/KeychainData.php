<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use stdClass;

class KeychainData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_keychain', 'keychainCode');
    }

    /**
     * @throws PhonicsException if ill-formed SQL
     */
    public function _create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_keychain` (
	`keychainCode` VARCHAR(32) NOT NULL,
	`fileName` VARCHAR(32) NOT NULL,
	`friendlyName` VARCHAR(32) NOT NULL,
	`groupCode` VARCHAR(32) NULL DEFAULT NULL,
	PRIMARY KEY (`keychainCode`),
	INDEX `FK_keychain__group` (`groupCode`),
	CONSTRAINT `FK_keychain__group` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);

    }

    /**
     * Get the records matching the tag, which is a groupCode, keychainCode, animal name, or numeric part of groupCode
     * @param int|string $tag
     * @return stdClass|null a stdClass version of the record
     * @throws PhonicsException if ill-formed SQL
     */
    public function get($tag): ?stdClass
    {
        $where = $this->processWhereClause($tag);
        if (empty($where)) {
            return null;
        } else {
            $query = 'SELECT * FROM abc_keychain WHERE '. $where;
            $object = $this->throwableQuery($query, QueryType::SINGLE_OBJECT);
        }
        return $object;
    }

    /**
     * @return int
     * @throws PhonicsException on ill-formed SQL
     */
    public function populate(): int
    {
        // Don't do anything if records exist
        if ($this->getCount() > 0) return 0;

        $animals = [
            'elephant', 'giraffe', 'monkey', 'tiger', 'lion', 'zebra', 'owl', 'fox', 'chipmunk', 'raccoon', 'fawn', 'hedgehog'
        ];
        for ($i=0; $i < count($animals); $i++) {
            $this->insertOrUpdate($i + 1, $animals[$i]);
        }
        return $this->getCount();
    }



    /**
     * @param int $ordinal
     * @param string $animalName
     * @return int affected records
     * @throws PhonicsException if ill-formed SQL
     */
    public function insertOrUpdate(int $ordinal, string $animalName): int
    {
        $key = Util::paddedNumber('k', $ordinal);
        $filename = strval($ordinal) . '.png';
        $friendlyName = ucfirst($animalName) . ' Keychain';
        $groupCode = Util::paddedNumber('G', $ordinal);

        $query = <<<EOT
        INSERT INTO `abc_keychain` (`keychainCode`, `fileName`, `friendlyName`, `groupCode`) 
        VALUES ('$key', '$filename', '$friendlyName', '$groupCode')
        ON DUPLICATE KEY UPDATE
        friendlyName = '$friendlyName';
EOT;
        return $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * Delete the keychain record with a matching tag. If an integer it will be converted to a keychain code.
     * if $tag is a valid keychainCode or GroupCode it will delete the matching record.
     * if $tag is all alpha the first letter will be capitalized and matched with the start of a friendly name.
     * if
     * @param int|string $tag
     * @return int affected records
     * @throws PhonicsException if ill-formed SQL
     */
    public function delete($tag): int
    {
        $where = $this->processWhereClause($tag);
        if (empty($where)) {
            $count = 0;
        } else {
            $query = $this->getDeleteBase() . $where;
            $count = $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
        }
        return $count;
    }

    /**
     * returns a where clause after determining if tag represents a groupCode, keychainCode, an animal name,
     * or the numeric part of a keychainCode.
     * @param int|string $tag something that uniquely identifies the desired record
     * @return string
     */
    private function processWhereClause($tag): string
    {
        $where = '';
        if (is_numeric($tag)) {
            $key   = Util::paddedNumber('k', $tag);
            $where = "keychainCode='$key'";
        } elseif (is_string($tag)) {
            if (Regex::isMatch(Regex::KEYCHAIN_CODE_PATTERN, $tag)) {
                $where = "keychainCode='$tag' OR groupCode='$tag'";
            } elseif (Regex::isMatch(Regex::NAME_PATTERN, $tag)) {
                $ucName = ucfirst($tag);
                $where = "friendlyName LIKE '$ucName%'";
            }
        }
        return $where;
    }
}
