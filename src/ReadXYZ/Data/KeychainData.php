<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;

class KeychainData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_keychain', 'keychainCode');
    }

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

    public function populate(): int
    {
        $query = <<<EOT
        INSERT INTO `abc_keychain` (`keychainCode`, `fileName`, `friendlyName`, `groupCode`) VALUES
            ('k1', '1.png', 'Elephant Keychain', 'G01'),
            ('k2', '2.png', 'Giraffe Keychain', 'G02'),
            ('k3', '3.png', 'Monkey Keychain', 'G03'),
            ('k4', '4.png', 'Tiger Keychain', 'G04'),
            ('k5', '5.png', 'Lion Keychain', 'G05'),
            ('k6', '6.png', 'Zebra Keychain', 'G06'),
            ('k7', '7.png', 'Owl Keychain', 'G07'),
            ('k8', '8.png', 'Fox Keychain', 'G08'),
            ('k9', '9.png', 'Chipmunk Keychain', 'G09'),
            ('ka', '10.png', 'Racoon Keychain', 'G10'),
            ('kb', '11.png', 'Fawn Keychain', 'G11'),
            ('kc', '12.png', 'Hedgehog Keychain', 'G12')
EOT;
        return $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
    }

}
