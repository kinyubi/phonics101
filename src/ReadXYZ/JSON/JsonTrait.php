<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Enum\BoolEnumTreatment;
use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Timer;

trait JsonTrait
{
    protected array  $persisted             = [
        'map'           => [],
        'jsonVersion'   => '',
        'booleanFields' => [],
        'primaryKey'    => '',
        'sourceModDate' => 0,
        'objects'       => []
    ];
    protected bool   $cachingEnabled        = false;
    protected bool   $cacheUsed             = false;
    protected string $sourceFile = '';
    protected string $cacheFile             = '';
    protected float  $cachedConstructTime   = 0;
    protected float  $uncachedConstructTime = 0;

// ======================== STATIC METHODS =====================
    public static function clearCache(): void
    {
        $glob = glob(__DIR__ . '/data/*.cache');
        foreach ($glob as $file) {
            unlink($file);
        }
    }


    /**
     * Takes a file or a json string, and puts the json into the format used when exporting mySQL data to JSON.
     * If already in that format, it's OK. The resultant JSON is saved to the specified output file in the 'data'
     * subdirectory.
     * @param string|array $source a filename or a JSON string or an array
     * @param string $tableName
     * @param string $outputFileName
     * @throws PhonicsException
     */
    public static function convert($source, string $tableName, string $outputFileName)
    {
        if (is_array($source)) {
            if (isAssociative($source)) {
                $data = array_values($source);
            } else {
                $data = $source;
            }
        } else {
        if (strlen($source) < 100) {
            $json    = file_get_contents($source);
            $version = JsonTrait::getImplicitVersion($source);
        } else {
            $json    = $source;
            $version = JsonTrait::getImplicitVersion();
        }
            $data    = JsonDecode::decode($json, JsonDecode::RETURN_STDCLASS);
        }


        $objects = [];
        foreach ($data as $item) {
            $objects[] = $item;
        }
        $targetFile = __DIR__ . "/data/$outputFileName";
        $data       = ['data' =>
                           [
                               ['type' => 'header', 'comment' => $tableName],
                                    ['type' => 'database', 'name' => 'readxyz0_phonics'],
                                    [
                                        'type'     => 'table',
                                        'name'     => $tableName,
                                   'version'  => self::timeToVersion(),
                                        'database' => 'readxyz0_phonics',
                                        'data'     => $objects]
                           ]
        ];
        $object     = (object)$data;
        $json       = json_encode($object->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($targetFile, $json);
    }

    /**
     * convert file modified date to version string of format. if file is empty use current data.
     * <last-digit-of-year>.<2-digit-month><2-digit-day>.<revision-number(0-9)>
     * @param string $fileName
     * @return string
     */
    public static function getImplicitVersion(string $fileName = ''): string
    {
        if (empty($fileName)) {
            return self::timeToVersion();
        } elseif ( ! file_exists($fileName)) {
            return '';
        } else {
            return self::timeToVersion(filemtime($fileName));
        }
    }

    public static function getInstance()
    {
        $class = __CLASS__;
        if ( ! isset(self::$instance)) {
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * convert epoch time to version string of the format Y.MMDD.N
     * @param int $epochTime
     * @return string
     */
    public static function timeToVersion(int $epochTime = 0): string
    {
        if ($epochTime == 0) {
            $epochTime = time();
        }

        return substr(date('y.md', $epochTime), 1);
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @param string $keyValue
     * @return bool
     */
    public function exists(string $keyValue): bool
    {
        return $this->get($keyValue) != null;
    }

    /**
     * @param string $key
     * @return object|string|null
     */
    public function get(string $key)
    {
        return $this->persisted['map'][$key] ?? null;
    }

    /**
     * return a mapped array of elements
     * @return array
     */
    public function getAll(): array
    {
        return $this->persisted['map'];
    }

    /**
     * the number of elements in the map
     * @return int
     */
    public function getCount(): int
    {
        return count($this->persisted['map']);
    }

// ======================== PROTECTED METHODS =====================
    /**
     * JsonTrait constructor. We only populate primaryKey, json, objects and map if primary key is provided,
     * otherwise caller is responsible for overriding MapMap and populating those fields
     * @param string $sourceFile
     * @param string $primaryKey
     * @throws PhonicsException
     */
    protected function baseConstruct(string $sourceFile, string $primaryKey)
    {
        $dataDir          = __DIR__ . '/data/';
        $this->sourceFile = $dataDir . $sourceFile;

        if ( ! file_exists($this->sourceFile)) {
            throw new PhonicsException("$sourceFile does not exist.");
        }
        $jsonModTime = filemtime($this->sourceFile);
        if ($this->cachingEnabled) {
            $this->cacheFile = $this->sourceFile . '.cache';

            if (file_exists($this->cacheFile)) {
                $cacheModTime = filemtime($this->cacheFile);
                if ($cacheModTime > $jsonModTime) {
                    $this->cacheUsed = true;
                }
            }
            if ($this->cacheUsed) {
                $timer           = $this->startTimer('retrieving cached ' . __CLASS__ . '.');
                $this->persisted = include($this->cacheFile);
                $this->stopTimer($timer);
            }
        }
        if (not($this->cacheUsed)) {
            $this->persisted['sourceModDate'] = $jsonModTime;
            $this->persisted['sourceFile']    = $this->sourceFile;
            $this->persisted['primaryKey']    = $primaryKey;
            $json                             = file_get_contents($this->sourceFile);
            $this->persisted['objects']       = $this->importDataAsStdClass($json);
        }
        }

    /**
     * Minimal version of makeMap.
     * @return void
     */
    protected function baseMakeMap(): void
    {
        // only perform if we need to create cache
        if ($this->cacheUsed) {
            return;
        }
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        $key   = $this->persisted['primaryKey'];
        foreach ($this->persisted['objects'] as $object) {
            $this->persisted['map'][$object->$key] = $object;
        }
        $this->stopTimer($timer);
        $this->cacheData();
    }

    protected function cacheData(): void
    {
        // only perform if we need to create cache
        if ($this->cacheUsed) {
            return;
        }
        if ( ! $this->cachingEnabled) {
            return;
        }
        $timer = $this->startTimer('caching ' . __CLASS__ . '.');
        if (isset($this->persisted['objects'])) {
            unset($this->persisted['objects']);
        }
        file_put_contents($this->cacheFile, "<?php\nreturn " . var_export($this->persisted, true) . ";");
        $this->stopTimer($timer);
    }

    /**
     * returns table records as an associative array
     * @param string $json
     * @return array
     * @throws PhonicsException
     */
    protected function importDataAsAssociativeArray(string $json): array
    {
        return self::importDataFromSource($json, JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
    }

    /**
     * returns table records as an array of stdClass objects
     * @param string $json
     * @return array
     * @throws PhonicsException
     */
    protected function importDataAsStdClass(string $json)
    {
        return self::importDataFromSource($json, JsonDecode::RETURN_STDCLASS);
    }

    /**
     * base class for getDataAsStdClass and getDataAsAssociativeArray. If the JSON file is an export
     * from phpMyAdmin the data is in the third element of the returned array in the 'data' field.
     * @param string $json
     * @param bool $returnType
     * @return mixed
     * @throws PhonicsException
     */
    protected function importDataFromSource(string $json, bool $returnType = JsonDecode::RETURN_STDCLASS)
    {
        $data = JsonDecode::decode($json, $returnType);

        if ((JsonDecode::RETURN_STDCLASS == $returnType) && isset($data[2]->data)) {
            $this->persisted['version'] = $data[2]->version ?? '';
            $myData                       = $data[2]->data;
        } elseif ((JsonDecode::RETURN_ASSOCIATIVE_ARRAY == $returnType) && isset($data[2]['data'])) {
            $this->persisted['version'] = $data[2]['version'] ?? '';
            $myData                       = $data[2]['data'];
        } else {
            $myData = $data;
        }
        if (empty($this->persisted['version'])) {
            $this->persisted['version'] = self::timeToVersion($this->persisted['sourceModDate']);
        }
        if (not(empty($this->booleanField))) {
            for ($i = 0; $i < count($myData); $i++) {
                foreach ($this->persisted['booleanFields'] as $fieldName) {
                    $myData[$i]->$fieldName = BoolEnumTreatment::enumToBool($myData[$i]->$fieldName);
                }
            }
        }
        return $myData;
    }

    protected function startTimer(string $description): ?Timer
    {
        return ($this->cachingEnabled) ? new Timer($description, Util::isLocal()) : null;
    }

    protected function stopTimer(?Timer $timerObject): float
    {
        if ($timerObject == null) {
            return 0;
        }
        return $timerObject->stop();
    }
// ======================== PRIVATE METHODS =====================

}
