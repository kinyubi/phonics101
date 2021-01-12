<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Enum\BoolEnumTreatment;
use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

abstract class AbstractJson
{
    protected static object   $instance;


    protected string    $sourceFile             = '';
    protected string    $json                   = '';
    protected array     $booleanFields          = [];
    protected string    $version                = '';
    protected string    $implicitVersion        = '';
    protected array     $map                    = [];
    protected string    $primaryKey             = '';
    protected array     $objects                = [];
    protected bool      $doesJsonHaveVersioning = false;

    /**
     * AbstractJson constructor. We only populate primaryKey, json, objects and map if primary key is provided,
     * otherwise caller is responsible for overriding MapMap and populating those fields
     * @param string $sourceFile
     * @param string $primaryKey
     * @throws PhonicsException
     */
    protected function __construct(string $sourceFile, string $primaryKey = '')
    {
        $dataDir          = __DIR__ . '/data/';
        $this->sourceFile = $dataDir . $sourceFile;
        if ( ! file_exists($this->sourceFile)) {
            throw new PhonicsException("$sourceFile does not exist.");
        }
        $this->implicitVersion = self::getImplicitVersion($this->sourceFile);
        if ($primaryKey) {
            $this->primaryKey = $primaryKey;
            $this->json       = file_get_contents($this->sourceFile);
            $this->objects    = $this->importDataAsStdClass();
            $this->makeMap($this->objects);
        }
    }

// ======================== STATIC METHODS =====================

    /**
     * @param string $source a filename or a JSON string
     * @param string $tableName
     * @throws PhonicsException
     */
    public static function convert(string $source, string $tableName)
    {
        if (strlen($source) < 100) {
            $json    = file_get_contents($source);
            $version = AbstractJson::getImplicitVersion($source);
        } else {
            $json = $source;
            $version = AbstractJson::getImplicitVersion();
        }

        $data    = JsonDecode::decode($json, JsonDecode::RETURN_STDCLASS);
        $objects = [];
        foreach ($data as $item) {
            $objects[] = $item;
        }
        $targetFile = __DIR__ . "/data/$tableName";
        $object     = (object)['data' =>
                                   [['type' => 'header', 'comment' => $tableName],
                                    ['type' => 'database', 'name' => 'readxyz0_phonics'],
                                    [
                                        'type'     => 'table',
                                        'name'     => $tableName,
                                        'version'  => $version,
                                        'database' => 'readxyz0_phonics',
                                        'data'     => $objects]
                                   ]];
        $json       = json_encode($object->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($targetFile, $json);
    }

    /**
     * covert file modified date to version string of format. if file is empty use current data.
     * <last-digit-of-year>.<2-digit-month><2-digit-day>.<revision-number(0-9)>
     * @param string $fileName
     * @return string
     */
    public static function getImplicitVersion(string $fileName=''): string
    {
        if (empty($fileName)) {
            $stamp = date('y.md', time());
        } elseif ( ! file_exists($fileName)) {
            return '';
        } else {
            $stamp = date('y.md', filemtime($fileName));
        }
        return substr($stamp, 1) . '.1';
    }

    protected static function getInstanceBase(string $class)
    {
        $fullClass = 'App\\ReadXYZ\\JSON\\' . $class;
        if ( ! isset(self::$instance)) {
            self::$instance = new $fullClass();
        }

        return self::$instance;
    }

// ======================== PUBLIC METHODS =====================
    /**
     * @param string $key
     * @return object|null
     */
    public function get(string $key): ?object
    {
        return $this->map[$key] ?? null;
    }

    /**
     * return a mapped array of elements
     * @return array
     */
    public function getAll(): array
    {
        return $this->map;
    }

    /**
     * the number of elements in the map
     * @return int
     */
    public function getCount(): int
    {
        return count($this->map);
    }

// ======================== PROTECTED METHODS =====================
    /**
     * @throws PhonicsException
     */
    protected function fixMissingVersion(): void
    {
        if ($this->version) {
            return;
        }
        if ($this->doesJsonHaveVersioning == false) {
            return;
        }
        if (empty($this->implicitVersion)) {
            return;
        }

        $objects = JsonDecode::decode($this->json, JsonDecode::RETURN_STDCLASS);
        if ( ! isset($objects[2]->data)) {
            return;
        }

        $objects[2]->version = $this->implicitVersion;
        $json                = json_encode($objects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        rename($this->sourceFile, $this->sourceFile . '.bak');
        file_put_contents($this->sourceFile, $json);
        $this->version = $this->implicitVersion;
    }

    /**
     * returns table records as an associative array
     * @return array
     * @throws PhonicsException
     */
    protected function importDataAsAssociativeArray(): array
    {
        return self::importDataFromSource(JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
    }

    /**
     * returns table records as an array of stdClass objects
     * @return array
     * @throws PhonicsException
     */
    protected function importDataAsStdClass()
    {
        return self::importDataFromSource(JsonDecode::RETURN_STDCLASS);
    }

    /**
     * base class for getDataAsStdClass and getDataAsAssociativeArray. If the JSON file is an export
     * from phpMyAdmin the data is in the third element of the returned array in the 'data' field.
     * @param bool $returnType
     * @return mixed
     * @throws PhonicsException
     */
    protected function importDataFromSource(bool $returnType = JsonDecode::RETURN_STDCLASS)
    {
        $data = JsonDecode::decode($this->json, $returnType);

        if ((JsonDecode::RETURN_STDCLASS == $returnType) && isset($data[2]->data)) {
            $this->version                = $data[2]->version ?? '';
            $myData                       = $data[2]->data;
            $this->doesJsonHaveVersioning = true;
        } elseif ((JsonDecode::RETURN_ASSOCIATIVE_ARRAY == $returnType) && isset($data[2]['data'])) {
            $this->version                = $data[2]['version'] ?? '';
            $myData                       = $data[2]['data'];
            $this->doesJsonHaveVersioning = true;
        } else {
            $myData = $data;
        }
        if ($this->doesJsonHaveVersioning && empty($this->version)) {
            $this->fixMissingVersion();
        }
        if (not(empty($this->booleanFields))) {
            for ($i = 0; $i < count($myData); $i++) {
                foreach ($this->booleanFields as $fieldName) {
                    $myData[$i]->$fieldName =  BoolEnumTreatment::enumToBool($myData[$i]->$fieldName);
                }
            }
        }
        return $myData;
    }

    /**
     * @param stdClass[] $objects
     * @return void
     */
    protected function makeMap(array $objects): void
    {
        $key = $this->primaryKey;
        foreach ($objects as $object) {
            $this->map[$object->$key] = $object;
        }
    }

}
