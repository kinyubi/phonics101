<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;

class KeychainData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_keychain');
    }

    public function create(): void
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
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    public function populate(): BoolWithMessage
    {
        $query = <<<EOT
INSERT INTO `abc_keychain` (`keychainCode`, `fileName`, `friendlyName`, `groupCode`) VALUES
	('k1', '1.png', 'Elephant Keychain', 'G04'),
	('k2', '2.png', 'Giraffe Keychain', 'G08'),
	('k3', '3.png', 'Monkey Keychain', 'G12'),
	('k4', '4.png', 'Tiger Keychain', 'G16'),
	('k5', '5.png', 'Lion Keychain', 'G20'),
	('k6', '6.png', 'Zebra Keychain', 'G24'),
	('k7', '7.png', 'Owl Keychain', 'G28'),
	('k8', '8.png', 'Fox Keychain', 'G32'),
	('k9', '9.png', 'Chipmunk Keychain', 'G36'),
	('ka', '10.png', 'Racoon Keychain', 'G40'),
	('kb', '11.png', 'Fawn Keychain', 'G44'),
	('kc', '12.png', 'Hedgehog Keychain', 'G48');
EOT;
        return $this->db->queryStatement($query);
    }

}
